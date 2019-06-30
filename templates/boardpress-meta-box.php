<label for="boardpress_card_id">
  <?= esc_html_e('Connect a Trello card to this post:') ?>
</label>
<select name="boardpress_card_id" id="boardpress_card_id" class="postbox">
    <option value="0"><?= esc_html_e('Not defined') ?></option>
    <?php foreach ($cards['cards'] as $card) { ?>
            <option
              value="<?= esc_attr($card['id']) ?>"
              <?= $current_card_id == $card['id'] ? 'selected' : '' ?>
            >
              <?= esc_html($card['name']) ?>
            </option>
    <?php } ?>
</select>
