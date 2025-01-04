<div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom">
    <h4><?= apply_filters('export_anything_section_heading', $heading) ?></h4>
    <div class="actions d-flex align-items-center">
        <?php foreach($actions as $action): 
            $action_url = admin_url('admin.php?page=' . EXPORT_ANYTHING_SLUG . '&action=' . $action['key']);
            if(isset($action['args'])) {
                $args = $action['args'];
                foreach($args as $key => $arg) {
                    $action_url .= '&' . $key . "=" . $arg;
                }
            }
            $data_attr = '';
            if(isset($action['data'])) {
                $data = $action['data'];
                foreach($data as $key => $value) {
                    $data_attr .= ' data-' . $key . '="' . $value . '"';
                }
            }
            ?>
            <a href="<?= $action_url ?>" class="btn btn-<?= $action['type'] ?> <?= $action['key'] ?>" <?= $data_attr ?>><?= $action['label'] ?></a>
        <?php endforeach; ?>
    </div>
</div>