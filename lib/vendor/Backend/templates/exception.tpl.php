<h2>Result: Exception</h2>
<div class="error large">
    <h3>
        {{ result.getMessage }}
    </h3>
    <label class="span-3">Code</label>
    {{ result.getCode() }}<br>
    <label class="span-3">File</label>
    {{ result.getFile() }}<br>
    <label class="span-3">Line</label>
    {{ result.getLine() }}<br>
</div>
<div class="notice">
    <h3>Stack Trace</h3>
    <ol class="bottom">
        {% for line in result.getTrace %}
            <li>{{ line.file }} ({{ line.line }})</li>
        {% endfor %}
    </ol>
</div>
{% if result.xdebug_message is empty %}
{% else %}
    <div class="notice">
        <h3>XDebug</h3>
        <table>{{ result.xdebug_message|raw }}</table>
    </div>
{% endif %}
