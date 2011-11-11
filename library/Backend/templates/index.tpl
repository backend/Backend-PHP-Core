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
        </head>
    <body>
        <div class="container">
            {% for contentBlock in content %}
                {% if contentBlock is not empty %}
                <!--ContentBlock-{{ loop.index0 }} -->
                    {{ contentBlock|raw }}
                <!--/ContentBlock-{{ loop.index0 }} -->
                {% endif %}
            {% endfor %}
        </div>
    </body>
</html>
