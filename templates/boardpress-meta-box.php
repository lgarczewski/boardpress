<label for="boardpress_card_id">
  <?= __('Connect a Trello card to this post:') ?>
</label>
<select name="boardpress_card_id" id="boardpress_card_id" class="postbox">
    <option value="0"><?= __('Not defined') ?></option>
    <?php foreach ($cards['cards'] as $card) { ?>
            <option
              value="<?= $card['id'] ?>"
              <?= $current_card_id == $card['id'] ? 'selected' : '' ?>
            >
              <?= $card['name'] ?>
            </option>
    <?php } ?>
</select>
