---
layout: page
title: Articles
permalink: /articles/
---

posts tagged 'article' - mostly from my stint at the Diamondback, UMD's student rag. 


<ul>
{% for post in site.posts %}
  {% if post.categories contains 'articles' %}
  <li><a href="{{ post.url }}">{{ post.title }}</a></li>
  {% endif %}
{% endfor %}
</ul>