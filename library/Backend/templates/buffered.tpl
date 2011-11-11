{% if buffered|length > 0 %}
    <div class="notice" id="buffered">
        {{ buffered|raw }}
    </div>
{% endif %}
