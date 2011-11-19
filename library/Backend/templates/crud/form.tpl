{% set fields = model.getAttributes %}
{% for key, value in fields %}
    <div>
        <label class="span-4">{{ key }}</label>
        <input value="{{ value }}">
    </div>
{% endfor %}
