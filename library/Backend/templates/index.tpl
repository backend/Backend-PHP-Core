<!DOCTYPE html>
<html>
    <head>
        <title>{{ mainTitle|default("Unknown")|striptags|raw }} - {{ title|striptags|raw }}</title>
        <link rel="stylesheet" href="{{ SITE_SUB_FOLDER }}css/blueprint/screen.css"
            type="text/css" media="screen, projection">
        <link rel="stylesheet" href="{{ SITE_SUB_FOLDER }}css/blueprint/print.css"
            type="text/css" media="print">
        <!--[if IE]>
        <link rel="stylesheet" href="{{ SITE_SUB_FOLDER }}css/blueprint/ie.css"
            type="text/css" media="screen, projection">
        <![endif]-->
        <link rel="stylesheet" href="{{ SITE_SUB_FOLDER }}css/basic.css"
            type="text/css" media="screen, projection">
    </head>
    <body>
        <div id="header" class="prepend-top">
            <div class="container">
                <h1>{{ mainTitle|default("Unknown") }}</h1>
                <h3>{{ title }}</h3>
            </div>
            <hr class="space">
        </div>
        <div class="container">
            {% for contentBlock in content %}
                {% if contentBlock is not empty %}
                <!--ContentBlock-{{ loop.index0 }} -->
                    {{ contentBlock|raw }}
                <!--/ContentBlock-{{ loop.index0 }} -->
                {% endif %}
            {% endfor %}
        </div>
        <div id="footer" class="quiet">
            <div class="container">
                <div class="span-4 right">
                    Status Code: {{ response.statusCode|default("Unknown") }}
                </div>
            </div>
        </div>
    </body>
</html>
