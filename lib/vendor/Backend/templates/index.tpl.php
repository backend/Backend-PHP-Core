<!DOCTYPE html>
<html>
    <head>
        <title>{{ mainTitle }} - {{ title }}</title>
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
            {{ content|raw }}
            {% if buffered|length > 0 %}
                <div class="notice" id="buffered">
                    {{ buffered|raw }}
                </div>
            {% endif %}
        </div>
    </body>
</html>
