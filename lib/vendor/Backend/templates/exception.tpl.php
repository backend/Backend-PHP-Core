<h2>Result: Exception</h2>
<div class="error large">
    <h3>
        {{ result.getMessage }}
    </h3>
    <label class="span-3">Code</label>
    {{ result.getCode() ?><br>
    <label class="span-3">File</label>
    {{ result.getFile() ?><br>
    <label class="span-3">Line</label>
    {{ result.getLine() ?><br>
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
