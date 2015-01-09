---
layout: page
title: Rob Cobb
permalink: /blog/
---

Thoughts, essays, stories, articles over the years. Many posts were originally somewhere else - they're linked.

<ul>
  {% for post in site.posts %}
    <li>
      <a href="{{ post.url }}">{{ post.title }}</a>
      {{ post.excerpt }}
    </li>
  {% endfor %}
</ul>