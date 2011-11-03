<?php
$result = $this->_view->get('result');
if ($result instanceof Exception) {
    $title = 'Result: Exception';
} else {
    $title  = get_class($this->_view->get('modelObj'));
}
?><!DOCTYPE HTML>
<html>
    <head>
        <title>HtmlView - <?php echo $title ?></title>
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
            <?php if ($result instanceof Exception): ?>
                <h2>Result: Exception</h2>
                <div class="error large">
                    <h3>
                        <?php echo $result->getMessage() ?>
                    </h3>
                    <label class="span-3">Code</label>
                    <?php echo $result->getCode() ?><br>
                    <label class="span-3">File</label>
                    <?php echo $result->getFile() ?><br>
                    <label class="span-3">Line</label>
                    <?php echo $result->getLine() ?><br>
                </div>
                <div class="notice">
                    <h3>Stack Trace</h3>
                    <ol class="bottom">
                        <?php foreach ($result->getTrace() as $line): ?>
                            <li>
                                <?php echo $line['file'] . '(' . $line['line'] . ')' ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <?php if (!empty($result->xdebug_message)): ?>
                    <div class="notice">
                        <h3>XDebug</h3>
                        <table><?php echo $result->xdebug_message ?></table>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <h2>Result</h2>
                <?php var_dump($this->_view->get('result')) ?>
            <?php endif; ?>
            <div class="notice" id="buffered">
                #buffered#
            </div>
        </div>
    </body>
</html>
