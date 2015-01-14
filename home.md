---
layout: page
title: Blog on the Cobb
permalink: /blog/
---

Some posts were originally somewhere else - they're linked to the originals in the post body. 
I've come pretty far as a writer. Some of the posts, especially the earlier ones, are not very good. I'll leave them up for you to see, in case evidence of progress inspires you, or you like it.

<ul>
  {% for post in site.posts %}
      <a class="post-link" href="{{ post.url }}">{{ post.title }}</a>
      <span class="description">{{ post.description }}</span><br><br>
  {% endfor %}
</ul>
