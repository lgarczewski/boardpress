<select name="trellopress[board_id]" id="trellopress[board_id]">
<?php foreach( $data['boards'] as $board ) { ?>
  <option
    value='<?= $board['id'] ?>'
    <?= $this->options['board_id'] == $board['id'] ? 'selected' : '' ?>
  >
    <?= $board['name'] ?>
  </option>
<?php } ?>
</select>
