<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 06/01/2018
 * Time: 00:44
 */

namespace Framework\Http\Responses;


use Framework\Exceptions\CookieVariableNotFound;
use Framework\Exceptions\IncorrectDataType;
use Framework\Exceptions\UnexpectedStatusCode;
use Framework\Storage\CookieHandler;

/**
 * Class Response
    * Base class to represent a response send back by a controller or request
 * @package Framework\Responses
 */
class Response implements \ArrayAccess {
    /**
     * @var string
     *
     * Holds the content to send with the response
     */
    protected $content;

    /**
    * @var array
    *
    * List of all the cookies to set when sending the headers
    */
    protected $cookies = [];

    /**
     * @var CookieHandler
     *
     * Handles all of the cookies for this request
     */
    protected $cookie_handler;

    /**
     * @var int
     *
     * Integer storing the status code of the response
     */
    protected $status_code;

    /**
     * @var array
     *
     * List of headers to send with the response
     */
    protected $headers;

    /**
     * @var string
     *
     * Version of the HTTP defaulted to 1.0
     */
    protected $version;

    /**
     * @var string
     *
     * Charset of the response
     */
    protected $charset;

    /**
     * @var array
     *
     * Associative array holding the response text for each response code
     */
    protected $status_texts = [
        100 => "Continue",
        101 => "Switching Protocols",
        102 => "Processing",            // RFC2518
        103 => "Early Hints",
        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-Authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",
        207 => "Multi-Status",          // RFC4918
        208 => "Already Reported",      // RFC5842
        226 => "IM Used",               // RFC3229
        300 => "Multiple Choices",
        301 => "Moved Permanently",
        302 => "Found",
        303 => "See Other",
        304 => "Not Modified",
        305 => "Use Proxy",
        307 => "Temporary Redirect",
        308 => "Permanent Redirect",    // RFC7238
        400 => "Bad Request",
        401 => "Unauthorized",
        402 => "Payment Required",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required",
        408 => "Request Timeout",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed",
        413 => "Payload Too Large",
        414 => "URI Too Long",
        415 => "Unsupported Media Type",
        416 => "Range Not Satisfiable",
        417 => "Expectation Failed",
        418 => "I\'m a teapot",                                               // RFC2324
        421 => "Misdirected Request",                                         // RFC7540
        422 => "Unprocessable Entity",                                        // RFC4918
        423 => "Locked",                                                      // RFC4918
        424 => "Failed Dependency",                                           // RFC4918
        425 => "Reserved for WebDAV advanced collections expired proposal",   // RFC2817
        426 => "Upgrade Required",                                            // RFC2817
        428 => "Precondition Required",                                       // RFC6585
        429 => "Too Many Requests",                                           // RFC6585
        431 => "Request Header Fields Too Large",                             // RFC6585
        451 => "Unavailable For Legal Reasons",                               // RFC7725
        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Timeout",
        505 => "HTTP Version Not Supported",
        506 => "Variant Also Negotiates",                                     // RFC2295
        507 => "Insufficient Storage",                                        // RFC4918
        508 => "Loop Detected",                                               // RFC5842
        510 => "Not Extended",                                                // RFC2774
        511 => "Network Authentication Required",                             // RFC6585
    ];

    /**
     * HttpResponse constructor.
     * @param string | null $content - Content of the response (e.g. HTML)
     * @param int $status_code - Status code of the response (default is 200 which is 'OK')
     * @param array $headers - Array of headers for the response
     * @throws UnexpectedStatusCode
     */
    public function __construct(string $content = null, int $status_code = 200, array $headers = []) {
        $this->content = $content;
        $this->headers = $headers;
        $this->version = "1.0";
        $this->charset = "utf-8";

        $this->cookie_handler = new CookieHandler();

        $this->setStatusCode($status_code);

        // Set content type by default if the user does not specify it
        if( !array_key_exists("Content-Type", $this->headers) or
            !array_key_exists("Content-type", $this->headers) or
            !array_key_exists("content-Type", $this->headers) or
            !array_key_exists("content-type", $this->headers)) {
            $this->headers["Content-Type"] = "text/html; charset={$this->charset}";
        }
    }

    // _____________________________________________ ArrayAccess _______________________________________________________

    /**
     * @inheritdoc
     */
    public function offsetGet($offset) {
        return $this->headers[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->headers[] = $value;
        } else {
            $this->headers[$offset] = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset) {
        unset($this->headers[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset) {
        return isset($this->headers[$offset]);
    }

    // ___________________________________________ End ArrayAccess _____________________________________________________

    /**
     * Sends the headers and content to the web page
     */
    public function send() {
        $this->sendHeaders();
        $this->sendContent();
    }

    /**
     * @return null|string - Returns the content of the response
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @param string $content - Sets the content of the response as a string
     */
    public function setContent($content) {
        $this->content = (string)$content;
    }

    /**
     * Sends the content of the response to the page
     */
    public function sendContent() {
        echo $this->content;
    }

    /**
     * Returns a list of all the cookies to be set
     */
    public function getCookies() {
        return $this->cookies;
    }

    /**
     * Returns a single cookie form a given name
     *
     * @param string $name - Name of cookie to search for
     * @return mixed - Returns the given cookie
     * @throws CookieVariableNotFound - Thrown if a cookie is not found with the given name
     */
    public function getCookie($name) {
        foreach ($this->cookies as $cookie) {
            if($cookie["name"] == $name) return $cookie;
        }
        throw new CookieVariableNotFound("Cookie with the name $name was not found");
    }

    /**
     * Sets a new cookie
     *
     * @param string $name - Name to store cookie under
     * @param string $value - Value to store
     * @param int $expire - The time the cookie will expire
     * @param null|string $path - The path of the server in which the cookie will be available from
     * @param string|null $domain - The (sub)domain that the cookie is available too
     * @param bool $secure - Indicates if the cookie should only be sent over HTTPS connection (if true then the cookie will only be send over a secure connection)
     * @param bool $http_only - Indicates whether the cookie is available only via the HTTP protocol (if ttrue then it will only be made accessible through the HTTP protocol)
     * @param bool $raw - Indicates whether the cookie should be set raw or not
     */
    public function setCookie(string $name, string $value = null, $expire = 0, string $path = '/', string $domain = null, bool $secure = false, bool $http_only = true, bool $raw = false) {
        $this->cookies[] = [
            "name" => $name,
            "value" => $value,
            "expire" => $expire,
            "path" => $path,
            "domain" => $domain,
            "secure" => $secure,
            "http_only" => $http_only,
            "raw" => $raw,
        ];
    }

    /**
     * @return array - Returns all the headers of this response
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * @param string $header - Header name to look for in the headers array
     * @return string - Returns a given header
     */
    public function getHeader($header) {
        return $this->headers[$header];
    }

    /**
     * Allows the user to set a header to return with the response
     * @param string $header - Header name
     * @param string $value - Value of the header
     */
    public function setHeader($header, $value) {
        $this->headers[$header] = $value;
    }

    /**
     * This is the same as 'setHeader' except this allows for multiple headers to be set at once
     * @param array $headers - List of headers to add to the current headers array
     */
    public function setHeaders(...$headers) {
        $this->headers += $headers;
    }

    /**
     * This either returns is the headers have already been send or it sends all the headers defined the headers array
     */
    public function sendHeaders() {
        if(headers_sent()) return;

        if ("HTTP/{$this->version}" != $_SERVER["SERVER_PROTOCOL"])
            $this->version = "1.1";

        header("HTTP/{$this->version} {$this->status_code} {$this->status_texts[$this->status_code]}");

        foreach ($this->headers as $key => $value) {
            if ($key == "Content-Type")
                $this->insertCharsetIfNotThere($value);

            header("$key: $value");
        }

        // Sets all the cookies
        foreach ($this->cookies as $cookie) {
            $this->cookie_handler->setCookie(
                $cookie["name"],
                $cookie["value"],
                $cookie["expire"],
                $cookie["path"],
                $cookie["domain"],
                $cookie["secure"],
                $cookie["http_only"],
                $cookie["raw"]
            );
        }
    }

    /**
     * @return int - Returns the status code of the response
     */
    public function getStatusCode() {
        return $this->status_code;
    }

    /**
     * This allows the user to set the status code of the http response
     * @param int $code - The status code of the http response
     * @throws UnexpectedStatusCode - Thrown if the status code given is not an integer or it is an invalid code
     */
    public function setStatusCode($code) {
        if(is_int($code) and $code >= 100 and $code <= 599)
            $this->status_code = (int)$code;
        else
            throw new UnexpectedStatusCode("Status code ($code) must be an integer and must be a valid status code");
    }

    /**
     * @return int - Returns the charset of the response
     */
    public function getCharset() {
        return $this->status_code;
    }

    /**
     * This allows the user to set the charset of the http response
     * @param string $charset - Charset to set
     * @throws IncorrectDataType - Thrown if the charset given was not a string
     */
    public function setCharset($charset) {
        if(is_string($charset))
            $this->charset = $charset;
        else
            throw new IncorrectDataType("Charset must be a string");
    }

    /**
     * @return int - Returns the HTTP version for the response
     */
    public function getHTTPVersion() {
        return $this->status_code;
    }

    /**
     * This allows the user to set the HTTP version which if defaulted to 1.0
     * @param string $version - Version to give the http response
     * @throws IncorrectDataType - Thrown in the version given was not a string
     */
    public function setHTTPVersion($version) {
        if(is_string($version))
            $this->version = $version;
        else
            throw new IncorrectDataType("Version must be a string");
    }

    /**
     * This adds 'charset' to the 'Content-Type' if it not already there
     * @param string $value - Value to search for charset in
     */
    private function insertCharsetIfNotThere(&$value) {
        if(strpos(strtolower($value), "charset") === false) {
            $value .= "; charset={$this->charset}";
        }
    }
}