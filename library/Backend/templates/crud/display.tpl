<h3>{{ model.getName() }}</h3>
{% set values = model.getAttributes %}
{% for key, value in values %}
    <div>
        <label class="span-4">{{ key }}</label>
            {{ value }}
    </div>
{% endfor %}
