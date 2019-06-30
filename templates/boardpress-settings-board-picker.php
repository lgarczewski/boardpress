<select name="boardpress[board_id]" id="boardpress[board_id]">
<?php foreach( $data['boards'] as $board ) { ?>
  <option
    value='<?= esc_attr($board['id']) ?>'
<?= isset($this->options['board_id']) && $this->options['board_id'] == $board['id'] ? 'selected' : '' ?>
  >
    <?= esc_html($board['name']) ?>
  </option>
<?php } ?>
</select>
