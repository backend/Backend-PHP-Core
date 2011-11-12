<h2>Exception</h2>
<div class="error large">
    <h3>
        {{ object.getMessage }}
    </h3>
    <label class="span-3">Code</label>
    {{ object.getCode() }}<br>
    <label class="span-3">File</label>
    {{ object.getFile() }}<br>
    <label class="span-3">Line</label>
    {{ object.getLine() }}<br>
</div>
<div class="notice">
    <h3>Stack Trace</h3>
    <ol class="bottom">
        {% for line in object.getTrace %}
            <li>{{ line.file }} ({{ line.line }})</li>
        {% endfor %}
    </ol>
</div>
{% if object.xdebug_message is empty %}
{% else %}
    <div class="notice">
        <h3>XDebug</h3>
        <table>{{ object.xdebug_message|raw }}</table>
    </div>
{% endif %}
