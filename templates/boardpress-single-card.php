<nav class="boardpress-single-card">
  <?= sprintf(
    __('Learn more about the task described in this post: %s', 'boardpress'),
    "<a class='trello-card-details' href='" . esc_url($data['url']) . "'>" .
      esc_html($data['name']) .
    "</a>"
  ) ?>
</nav>
