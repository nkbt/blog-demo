<?php

require_once 'includes/common.inc.php';


$page['css'][] = 'frame';
$page['js'][] = 'frame';

require 'includes/header.inc.php';


if (!isset($_GET['key'])) {
    ?>
    Invalid key
    <?php

    require 'includes/footer.inc.php';
    die;
}


$type = $redis->type($_GET['key']);
$exists = $redis->exists($_GET['key']);


?>
    <h2><?php echo format_html($_GET['key']) ?>
        <?php if ($exists) { ?>
            <a href="rename.php?s=<?php echo $server['id'] ?>&amp;key=<?php echo urlencode($_GET['key']) ?>"><img src="images/edit.png" width="16" height="16" title="Rename" alt="[R]"></a>
            <a href="delete.php?s=<?php echo $server['id'] ?>&amp;key=<?php echo urlencode($_GET['key']) ?>" class="delkey"><img src="images/delete.png" width="16" height="16" title="Delete" alt="[X]"></a>
            <a href="export.php?s=<?php echo $server['id'] ?>&amp;key=<?php echo urlencode($_GET['key']) ?>"><img src="images/export.png" width="16" height="16" title="Export" alt="[E]"></a>
        <?php } ?>
    </h2>
<?php

if (!$exists) {
    ?>
    This key does not exist.
    <?php

    require 'includes/footer.inc.php';
    die;
}


$alt = false;
$ttl = $redis->ttl($_GET['key']);

try {
    $encoding = $redis->object('encoding', $_GET['key']);
} catch (Exception $e) {
    $encoding = null;
}


switch ($type) {
    case Redis::REDIS_HASH:
        $values = $redis->hGetAll($_GET['key']);
        $size = count($values);
        break;

    case Redis::REDIS_LIST:
        $size = $redis->lLen($_GET['key']);
        break;

    case Redis::REDIS_SET:
        $values = $redis->sMembers($_GET['key']);
        $size = count($values);
        break;

    case Redis::REDIS_ZSET:
        $values = $redis->zRange($_GET['key'], 0, -1);
        $size = count($values);
        break;

    case Redis::REDIS_STRING:
    default:
        $value = $redis->get($_GET['key']);
        $size = strlen($value);
        break;

}


?>
    <table>

        <tr>
            <td>
                <div>Type:</div>
            </td>
            <td>
                <div><?php echo format_html($type) ?></div>
            </td>
        </tr>

        <tr>
            <td>
                <div><abbr title="Time To Live">TTL</abbr>:</div>
            </td>
            <td>
                <div><?php echo ($ttl == -1) ? 'does not expire' : $ttl ?> <a href="ttl.php?s=<?php echo $server['id'] ?>&amp;key=<?php echo urlencode($_GET['key']) ?>&amp;ttl=<?php echo $ttl ?>"><img src="images/edit.png" width="16" height="16" title="Edit TTL" alt="[E]" class="imgbut"></a></div>
            </td>
        </tr>

        <?php if (!is_null($encoding)) { ?>
            <tr>
                <td>
                    <div>Encoding:</div>
                </td>
                <td>
                    <div><?php echo format_html($encoding) ?></div>
                </td>
            </tr>
        <?php } ?>

        <tr>
            <td>
                <div>Size:</div>
            </td>
            <td>
                <div><?php echo $size ?> <?php echo ($type == 'string') ? 'characters' : 'items' ?></div>
            </td>
        </tr>

    </table>

<p>
<?php



// String
if ($type == Redis::REDIS_STRING) {
    ?>

    <table>
        <tr>
            <td>
                <div style="white-space: pre; font-size: 10px;"><?php
                    $decodedValue = json_decode($value, true);
                    if ($decodedValue !== null) {
                        $value = $decodedValue;
                    }
                    var_export($value)
                    ?>
                </div>
            </td>
            <td>
                <div>
                    <a href="edit.php?s=<?php echo $server['id'] ?>&amp;type=string&amp;key=<?php echo urlencode($_GET['key']) ?>"><img src="images/edit.png" width="16" height="16" title="Edit" alt="[E]"></a>
                </div>
            </td>
            <td>
                <div>
                    <a href="delete.php?s=<?php echo $server['id'] ?>&amp;type=string&amp;key=<?php echo urlencode($_GET['key']) ?>" class="delval"><img src="images/delete.png" width="16" height="16" title="Delete" alt="[X]"></a>
                </div>
            </td>
        </tr>
    </table>

<?php
} // Hash
else if ($type == Redis::REDIS_HASH) {
    ?>

    <table>
    <tr>
        <th>
            <div>Key</div>
        </th>
        <th>
            <div>Value</div>
        </th>
        <th>
            <div>&nbsp;</div>
        </th>
        <th>
            <div>&nbsp;</div>
        </th>
    </tr>

    <?php foreach ($values as $hkey => $value) { ?>
        <tr <?php echo $alt ? 'class="alt"' : '' ?>>
            <td>
                <div><?php echo format_html($hkey) ?></div>
            </td>
            <td>
                <div style="white-space: pre; font-size: 10px;"><?php
                    $decodedValue = json_decode($value, true);
                    if ($decodedValue !== null) {
                        $value = $decodedValue;
                    }
                    var_export($value)
                    ?>
                </div>
            </td>
            <td>
                <div>
                    <a href="edit.php?s=<?php echo $server['id'] ?>&amp;type=hash&amp;key=<?php echo urlencode($_GET['key']) ?>&amp;hkey=<?php echo urlencode($hkey) ?>"><img src="images/edit.png" width="16" height="16" title="Edit" alt="[E]"></a>
                </div>
            </td>
            <td>
                <div>
                    <a href="delete.php?s=<?php echo $server['id'] ?>&amp;type=hash&amp;key=<?php echo urlencode($_GET['key']) ?>&amp;hkey=<?php echo urlencode($hkey) ?>" class="delval"><img src="images/delete.png" width="16" height="16" title="Delete" alt="[X]"></a>
                </div>
            </td>
        </tr>
        <?php $alt = !$alt;
    } ?>

<?php
} // List
else {
    if ($type == Redis::REDIS_LIST) {
        ?>

        <table>
        <tr>
            <th>
                <div>Index</div>
            </th>
            <th>
                <div>Value</div>
            </th>
            <th>
                <div>&nbsp;</div>
            </th>
            <th>
                <div>&nbsp;</div>
            </th>
        </tr>

        <?php for ($i = 0; $i < $size; ++$i) {
            $value = $redis->lIndex($_GET['key'], $i);
            ?>
            <tr <?php echo $alt ? 'class="alt"' : '' ?>>
                <td>
                    <div><?php echo $i ?></div>
                </td>
                <td>
                    <div style="white-space: pre; font-size: 10px;"><?php
                        $decodedValue = json_decode($value, true);
                        if ($decodedValue !== null) {
                            $value = $decodedValue;
                        }
                        var_export($value)
                        ?>
                    </div>
                </td>
                <td>
                    <div>
                        <a href="edit.php?s=<?php echo $server['id'] ?>&amp;type=list&amp;key=<?php echo urlencode($_GET['key']) ?>&amp;index=<?php echo $i ?>"><img src="images/edit.png" width="16" height="16" title="Edit" alt="[E]"></a>
                    </div>
                </td>
                <td>
                    <div>
                        <a href="delete.php?s=<?php echo $server['id'] ?>&amp;type=list&amp;key=<?php echo urlencode($_GET['key']) ?>&amp;index=<?php echo $i ?>" class="delval"><img src="images/delete.png" width="16" height="16" title="Delete" alt="[X]"></a>
                    </div>
                </td>
            </tr>
            <?php $alt = !$alt;
        } ?>

    <?php
    } // Set
    else {
        if ($type == Redis::REDIS_SET) {

            ?>
            <table>
            <tr>
                <th>
                    <div>Value</div>
                </th>
                <th>
                    <div>&nbsp;</div>
                </th>
                <th>
                    <div>&nbsp;</div>
                </th>
            </tr>

            <?php foreach ($values as $value) {
                $display_value = $redis->exists($value) ? '<a href="view.php?s=' . $server['id'] . '&key=' . urlencode($value) . '">' . nl2br(format_html($value)) . '</a>' : nl2br(format_html($value));
                ?>
                <tr <?php echo $alt ? 'class="alt"' : '' ?>>
                    <td>
                        <div><?php echo $display_value ?></div>
                    </td>
                    <td>
                        <div>
                            <a href="edit.php?s=<?php echo $server['id'] ?>&amp;type=set&amp;key=<?php echo urlencode($_GET['key']) ?>&amp;value=<?php echo urlencode($value) ?>"><img src="images/edit.png" width="16" height="16" title="Edit" alt="[E]"></a>
                        </div>
                    </td>
                    <td>
                        <div>
                            <a href="delete.php?s=<?php echo $server['id'] ?>&amp;type=set&amp;key=<?php echo urlencode($_GET['key']) ?>&amp;value=<?php echo urlencode($value) ?>" class="delval"><img src="images/delete.png" width="16" height="16" title="Delete" alt="[X]"></a>
                        </div>
                    </td>
                </tr>
                <?php $alt = !$alt;
            } ?>

        <?php
        } // ZSet
        else {
            if ($type == Redis::REDIS_ZSET) {
                ?>

                <table>
                <tr>
                    <th>
                        <div>Score</div>
                    </th>
                    <th>
                        <div>Value</div>
                    </th>
                    <th>
                        <div>&nbsp;</div>
                    </th>
                    <th>
                        <div>&nbsp;</div>
                    </th>
                </tr>

                <?php foreach ($values as $value) {
                    $score = $redis->zScore($_GET['key'], $value);
                    $display_value = $redis->exists($value) ? '<a href="view.php?s=' . $server['id'] . '&key=' . urlencode($value) . '">' . nl2br(format_html($value)) . '</a>' : nl2br(format_html($value));
                    ?>
                    <tr <?php echo $alt ? 'class="alt"' : '' ?>>
                        <td>
                            <div><?php echo $score ?></div>
                        </td>
                        <td>
                            <div><?php echo $display_value ?></div>
                        </td>
                        <td>
                            <div>
                                <a href="edit.php?s=<?php echo $server['id'] ?>&amp;type=zset&amp;key=<?php echo urlencode($_GET['key']) ?>&amp;score=<?php echo $score ?>&amp;value=<?php echo urlencode($value) ?>"><img src="images/edit.png" width="16" height="16" title="Edit" alt="[E]"></a>
                                <a href="delete.php?s=<?php echo $server['id'] ?>&amp;type=zset&amp;key=<?php echo urlencode($_GET['key']) ?>&amp;value=<?php echo urlencode($value) ?>" class="delval"><img src="images/delete.png" width="16" height="16" title="Delete" alt="[X]"></a>
                            </div>
                        </td>
                    </tr>
                    <?php $alt = !$alt;
                } ?>

            <?php
            }
        }
    }
}


if ($type != Redis::REDIS_STRING) {
    ?>
    </table>

    <p>
        <a href="edit.php?s=<?php echo $server['id'] ?>&amp;type=<?php echo $type ?>&amp;key=<?php echo urlencode($_GET['key']) ?>" class="add">Add another value</a>
    </p>
<?php
}


require 'includes/footer.inc.php';

