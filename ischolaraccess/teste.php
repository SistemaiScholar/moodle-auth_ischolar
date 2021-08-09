<?php
function debug($valor, $nome="") {
    echo '<pre>';
    echo "<strong>$nome</strong> = ";
    echo var_export($valor, true);
    echo '</pre>';
    echo '<hr>';
}


global $CFG, $ADMIN, $DB, $SESSION, $USER, $SITE, $PAGE, $COURSE, $OUTPUT, $FULLME, $ME, $FULLSCRIPT, $SCRIPT;

debug($CFG, 'CFG');
debug(get_object_vars($CFG), 'cfg object_vars');
debug(get_class_methods($CFG), 'cfg object_methods');

debug($DB, 'DB');
debug(get_object_vars($DB), 'DB object_vars');
debug(get_class_methods($DB), 'DB object_methods');

debug($OUTPUT, 'OUTPUT');
debug(get_object_vars($OUTPUT), 'OUTPUT object_vars');
debug(get_class_methods($OUTPUT), 'OUTPUT object_methods');