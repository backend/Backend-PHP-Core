<h3>Something</h3>
{% for key, value in fields %}
    <div>
        <label class="span-4">{{ key }}</label>
            {{ value }}
    </div>
{% endfor %}
