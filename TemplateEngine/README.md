# PHP Framework Template Engine

Simple template syntax built for the PHP Web Framework.

# Syntax 

## Comment

Comment looks like this:

`{# Write comment here #}`

## Variables 

Variable looks like this:

`{{ $foo }} `

## Escaped Variable

Escaped variable, stop HTML content rendering and convert special characters into HTMl entities.
This is a wrapper for the builtin PHP [htmlspecialchars](http://php.net/manual/en/function.htmlspecialchars.php) function.

`{! $bar !}`

## If

If block checks whether the given condition is true, if so it will display the given code between the if block and the endif or the next else if block.
The else block is display is none of the given if/ else if blocks are true.

```
{% if $num >= 10 %}
    <h1>Higher or equal to 10</h1>
{% elseif $num < 10 and $num >= 5 %}
    <h1> Between 5 and 9</h1>
{% else %}
    <h1>Less than 5</h1>
{% endif %}
```

## For

For block displays the content between the for and end for tags. The content will be displayed as many time until the condition is false.


```
{% for $i = 0; $i <= 10; $i++ %}
    <span>{{ $i }}</span>
{% endforeach %} 
```
## For Each 

For each loops over an iterable object.  

```
{% foreach $test as $key => $value %}
    <span>{{ $key }} - {{ $value }}</span>
{% endforeach %}
```

## Empty 

Empty block is displayed if the given loop is empty. E.g. the given array is empty (`[]`).

```
{% foreach $test as $key => $value %}
    <span>{{ $key }} - {{ $value }}</span>
{% empty %}
    <span>Its empty</span>
{% endforeach %}
```

## Include 

Includes the given file.

`{% include "test.php" %}`

Includes the given file and injects the defined variables given into the included file.

`{% include "test.php" with test="test" number="25" %}`

## Static

Used to load static files. E.g CSS or images.

`{% static "images/test.png" %}`

## CSRF Token

This tag is used for CSRF protection.

`{% csrf_token %}`

## Extends

Means the current view extends a parent view. All content is the given file will be included before the extend tag.

`{% extends "views/template.php" %}`


## Define 

Signals where the block with the given name should be injected.

`{% define block_name %}`

## Block

Defines a block of content which is to be inserted where a define tag is used.

```
{% block block_name %}
        ...
{% endblock %} 
```

## Autoescape 

All content between the autoescape and end autoescape tags will be automatically escaped using the builtin PHP [htmlspecialchars](http://php.net/manual/en/function.htmlspecialchars.php) function. 

```
{% autoescape %}
    This will be autoescaped 
{% endautoescape %} 
```

## Spaceless

All whitespace will be removed between the spaceless and end spaceless tags 

``` 
{% spaceless %}
    <div>
        <strong>foo</strong>
    </div>
{% endspaceless %}
```
