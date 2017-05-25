<?php
/** @var \Spiral\NavigationBuilder\DefaultRenderer $renderer */
$renderer = spiral(\Spiral\NavigationBuilder\DefaultRenderer::class);

if (count($navigation)) { ?>
    <ul>
        <?php foreach ($navigation as $item) { ?>
            <li>
                <?= $renderer->link($item['link']) ?>
                <?= $renderer->navigation($item['sub']) ?>
            </li>
        <?php } ?>
    </ul>
<?php } ?>