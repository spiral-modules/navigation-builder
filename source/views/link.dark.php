<a href="<?= $link['href'] ?>" data-depth="<?= $link['depth'] ?>"
    <?php foreach ($link['attributes'] as $name => $value) {
        if (!is_scalar($value)) {
            continue;
        }

        echo $name . '="' . $value . '"';
    } ?>
><?= $link['text'] ?></a>