<?php foreach ($this->output as $id => $card) : ?>
<article class="trello-card post blog">
  <header class="entry-header">
    <h2>
      <a href="<?= esc_url($card['url']) ?>">
        <?= esc_html($card['name']) ?>
      </a>
    </h2>
    <div class="entry-meta trello-list">
      <?php foreach ( $card['labels'] as $label ) : ?>
      <span class="trello-label
      <?= isset($label['color']) ? 'trello-label-' . sanitize_html_class($label['color']) : '' ?>">
        <?= esc_html($label['name']) ?>
      </span>
      <?php endforeach; ?>
      <span class="trello-status trello-status-<?= sanitize_html_class($card['statslug']) ?>">
        <?= sprintf(__('Status: %s'), strtolower( esc_html($card['status']) )) ?>
      </span>
      <span>
        <?= sprintf(
          __('Last updated: %s', 'boardpress'),
          "<time datetime='{$card['last_update']['time']}'>
            {$card['last_update']['text']}
          </time>"
        ) ?>
      </span>
      <?php if ( !empty($card['related_posts']) ) : ?>
      <span>
        <a href="<?= BoardPressRelatedPosts::get_link( $id ) ?>">
        <?= sprintf(
          __('Mentioned in %d related posts', 'boardpress'),
          count( $card['related_posts'] )
        ) ?>
        </a>
      </span>
    <?php endif; ?>
    </div>
  </header>
  <div class="trello-desc">
    <?php if (!empty($card['cover'])) : ?>
    <img class="trello-cover" src="<?= $card['cover'] ?>" />
    <?php endif; ?>
    <?= $card['desc'] ?>
    <?php if ( !empty( $card['checklists']) ) : ?>
    <form>
      <?php foreach ($card['checklists'] as $cl ) : ?>
      <fieldset>
        <legend>
          <span class='trello-checklist-name'>
            <?= $cl['name'] ?>
          </span>
          <a class='trello-card-details' href="<?= $card['url'] ?>"
              title="<?= __('Takes you to the board on Trello.com', 'boardpress') ?>">
            <?= __('open task details', 'boardpress') ?>
          </a>
        </legend>
        <ul class="trello-checklist">
        <?php foreach ( $cl['checkItems'] as $item ) : ?>
          <li>
            <input type="checkbox" disabled
              <?= $item['state'] == 'complete' ? 'checked' : ''; ?> />
              <label><?= $item['name'] ?></label>
          </li>
        <?php endforeach; ?>
        </ul>
      </fieldset>
      <?php endforeach; ?>
    </form>
    <?php else: ?>
    <a class='trello-card-details' href='<?= $card['url'] ?>'
      title='<?= __('Opens the board on Trello.com', 'boardpress') ?>'>
        <?= __('open task details', 'boardpress') ?>
    </a>
    <?php endif; ?>
  </div>
</article>
<?php endforeach; ?>
