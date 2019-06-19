<nav class="trellopress-single-card">
  <?= sprintf(
    __('Learn more about the task described in this post: %s', 'trellopress'),
    "<a class='trello-card-details' href='{$data['url']}'>{$data['name']}</a>"
  ) ?>
</nav>
