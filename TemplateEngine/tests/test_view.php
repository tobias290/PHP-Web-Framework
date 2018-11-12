<html>
<head>
    <meta charset="UTF-8">
    <title>Test</title>
</head>
<body>
    {% extends "views/template.php" %}

    {% block body %}
        {% static "images/test.png" %}

        {# testing #}

        {% csrf_token %}

        {% include "test.php" with test="test" number="25" %}

        {% autoescape %}
            lalala
        {% endautoescape %}

        {% spaceless %}
            <div>
                <strong>foo</strong>
            </div>
        {% endspaceless %}


        <h1>{{ $test }}</h1>
        <h1>{{ $var }}</h1>
        <h1>{! $var !}</h1>

        {% if $test == "test" and !$not_test %}
            <h1>Hi</h1>
        {% endif %}

        {% foreach $test as $key => $value %}
            <span>...</span>
        {% empty %}
            <span>Its empty</span>
        {% endforeach %}

        {% foreach $test as $t %}
            <span>...</span>
        {% empty %}
            <span>Its empty</span>
        {% endforeach %}
    {% endblock %}
</body>
</html>