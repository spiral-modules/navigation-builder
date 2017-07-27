<extends:vault.layout title="[[Navigation]]" class="wide-content"/>

<?php #compile
/**
 * @var array $domains
 */
?>

<define:actions>
    <select name="domain" class="item-select js-select-domain">
        <?php foreach ($domains as $name => $domain) { ?>
            <option value="<?= $name ?>"><?= $domain ?></option>
        <?php } ?>
    </select>
</define:actions>

<define:content>
    <div class="card">
        <div class="card-content">
            <div class="row">
                <div class="col s12 m7">
                    <p>Drag and drop to reorder menu. Use checkboxes to enable disable section</p>
                </div>

                <div class="col s12 m4 offset-m1">
                    <p>Links are independent to navigation, if you change link title or URL it is changed everywhere it is used. Use different links if you need different titles for same URL.</p>
                </div>
            </div>
            <div class="row">
                <div class="col s12 m7">
                    <div class="editable-navigation js-editable-navigation js-sf-navigation-items" data-domain="default">
                        Loading navigation...
                    </div>
                </div>

                <div class="col s12 m4 offset-m1">
                    <div class="js-link-list js-sf-navigation-items">
                        Loading links...
                    </div>
                </div>
            </div>
        </div>
    </div>
</define:content>
