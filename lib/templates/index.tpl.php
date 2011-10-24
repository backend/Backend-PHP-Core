<!DOCTYPE HTML>
<html>
    <head>
        <title>HtmlView - <?php echo get_class($this->_view->get('modelObj')) ?></title>
            <link rel="stylesheet" href="#SITE_SUB_FOLDER#css/blueprint/screen.css"
                type="text/css" media="screen, projection">
            <link rel="stylesheet" href="#SITE_SUB_FOLDER#css/blueprint/print.css"
                type="text/css" media="print">
            <!--[if IE]>
            <link rel="stylesheet" href="#SITE_SUB_FOLDER#css/blueprint/ie.css"
                type="text/css" media="screen, projection">
            <![endif]-->
        </head>
    <body>
        <div class="container">
            <h3>Result</h3>
            <?php var_dump($this->_view->get('result')) ?>
            <div class="notice" id="buffered">
                #buffered#
            </div>
        </div>
    </body>
</html>
