<?php

namespace Hypersistence\Console;

use DB;
use Illuminate\Console\Command;

class CreateModelsHypersistence extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hypersistence:make-models 
                            {directory? : Create models into specified directory. Default: app/Models}
                            {--override : Override existing models into directory}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make models from database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $this->exec();
    }

    private function exec() {

        $this->comment("\nStarting Database connection!");

        $namespace = "";
        $use = "use Hypersistence\Hypersistence";
        $extends = "Hypersistence";
        $dir = !is_null($this->argument('directory')) ? $this->argument('directory') : 'app/Models';
        $override = $this->option('override');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $tmp = explode('/', $dir);
        foreach ($tmp as $v) {
            $namespace .= $this->camelCase($v) . "\\";
        }

        $namespace = substr($namespace, 0, -1);

        $db = DB::connection()->getPdo();

        $query = "show tables";

        $tables = [];

        if ($stmt = $db->prepare($query)) {
            if ($stmt->execute()) {
                while ($t = $stmt->fetch()) {
                    $table = reset($t);
                    if ($table != "migrations") {
                        $tables[$table]['name'] = $table;
                    }
                }
            }
        } else {
            $this->error("Error to get tables names!");
            exit;
        }
        $this->comment("\nCollecting meta data from Database!");
        $bar = $this->output->createProgressBar(count($tables));

        foreach ($tables as $t => $info) {
            $tables[$t]['fields'] = [];
            $query = "desc " . $t;
            $fields = [];
            if ($stmt = $db->prepare($query)) {
                if ($stmt->execute()) {
                    while ($f = $stmt->fetch()) {
                        $fieldsInfo = [];
                        $fieldsInfo['field'] = $f['Field'];
                        $fieldsInfo['type'] = $f['Type'];
                        $fieldsInfo['key'] = $f['Key'];
                        $fieldsInfo['column'] = true;
                        $tables[$t]['fields'][$fieldsInfo['field']] = $fieldsInfo;
                    }
                }
            } else {
                $this->error("Error to get fields of table " . $t . "!");
                exit;
            }
            $bar->advance();
        }

        $bar->finish();

        $this->comment("\n\nCollecting constranits from tables!");
        $bar = $this->output->createProgressBar(count($tables));

        $infoSchema = "INFORMATION_SCHEMA";

        $db->exec("USE $infoSchema");

        foreach ($tables as $t => $info) {
            $query = "SELECT TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
            FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
            WHERE TABLE_SCHEMA = '" . env("DB_DATABASE", "laravel_db") . "' 
            AND TABLE_NAME = '$t' 
            AND REFERENCED_COLUMN_NAME IS NOT NULL";
            if ($stmt = $db->prepare($query)) {
                if ($stmt->execute()) {
                    while ($i = $stmt->fetch()) {
                        $tableName = $i['TABLE_NAME'];
                        $colName = $i['COLUMN_NAME'];
                        $colNameAux = $i['COLUMN_NAME'];
                        $referenceTable = $i['REFERENCED_TABLE_NAME'];
                        $referenceCol = $i['REFERENCED_COLUMN_NAME'];
                        $isManyToMany = false;
                        if (count($tables[$tableName]['fields']) == 2) {
                            $isManyToMany = true;
                            foreach ($tables[$tableName]['fields'] as $field => $attrs) {
                                if ($attrs['key'] !== 'PRI' && $attrs['key'] != 'MUL') {
                                    $isManyToMany = false;
                                }
                            }
                        }
                        if ($isManyToMany) {
                            $infoManyToMany = $this->getManyToManyAnotation($tableName, $referenceTable, $colName, $db, env("DB_DATABASE", "laravel_db"), $namespace);
                            $tables[$referenceTable]['fields'][$infoManyToMany['field']]['relationship'] = $infoManyToMany['anotation'];
                            $tables[$referenceTable]['fields'][$infoManyToMany['field']]['field'] = $infoManyToMany['field'];
                            $tables[$referenceTable]['fields'][$infoManyToMany['field']]['column'] = false;
                            $tables[$tableName]['isManyToMany'] = true;
                        } else {
                            if(isset($tables[$tableName]['fields'][$colNameAux]['key']) && $tables[$tableName]['fields'][$colName]['key'] == 'PRI'){
                                $colNameAux = $this->camelCase($referenceTable, false);
                                $tables[$tableName]['fields'][$colNameAux]['field'] = $colNameAux;
                            } 
                            $tables[$tableName]['fields'][$colNameAux]['relationship'] = "\t* @manyToOne(lazy)\n\t* @itemClass(" . ($namespace != '' ? "$namespace\\" : "") . $this->camelCase($referenceTable) . ")";
                            if ($colName != "id") {
                                $field = $colName;
                                if (strtolower(substr($colName, -3)) == "_id") {
                                    $field = str_replace("_id", "", $colName);
                                } else if (strtolower(substr($colName, -2)) == "id") {
                                    $field = str_replace("id", "", $colName);
                                }
                                $tables[$tableName]['fields'][$colName]['field'] = $field;
                            }
                            $field = $this->camelCase($tableName, false);
                            $field .= substr($field, -1) != "s" ? "s" : '';

                            if (isset($tables[$referenceTable]['fields'][$referenceCol]['key'])) {
                                if(isset($tables[$referenceTable]['fields'][$field])) $field.="_$colNameAux";
                                $tables[$referenceTable]['fields'][$field]['relationship'] = "\t* @oneToMany(lazy)\n\t* @itemClass(" . ($namespace != '' ? "$namespace\\" : "") . $this->camelCase($tableName) . ")\n\t* @joinColumn(" . $colNameAux . ")";
                                $tables[$referenceTable]['fields'][$field]['column'] = false;
                                $tables[$referenceTable]['fields'][$field]['field'] = $field;
                            }
                        }
                    }
                }
            } else {
                $this->error("Error to get constraints of table " . $t . "!\n");
                exit;
            }
            $bar->advance();
        }
        $bar->finish();

        $this->comment("\n\nCreating Models");
        $bar = $this->output->createProgressBar(count($tables));

        foreach ($tables as $t => $data) {
            $className = $this->camelCase($t);
            $fileName = "$dir/$className.php";

            if (isset($data['isManyToMany']) || (file_exists($fileName) && !$override)) {
                continue;
            }

            $content = "<?php\n\n";
            $content .= ($namespace != "") ? "namespace $namespace;\n\n$use;\n\n" : '';
            $content .= "/**\n* @table($t)\n*/\n";
            $content .= "class $className extends $extends {\n\n";

            foreach ($data['fields'] as $f => $attrs) {
                $content .= "\t/**\n";
                $content .= isset($attrs['key']) && $attrs['key'] == 'PRI' ? "\t* @primaryKey\n" : "";
                $content .= isset($attrs['column']) && $attrs['column'] == true ? "\t* @column(" . $f . ")\n" : "";
                $content .= isset($attrs['relationship']) ? $attrs['relationship'] . "\n" : "";
                $content .= "\t*/\n";
                $content .= "\tprivate $" . $this->camelCase($attrs['field'], false) . ";\n\n";
            }


            foreach ($data['fields'] as $f => $attrs) {
                $content .= "\n" . $this->createGetsAndSets($attrs['field']);
            }

            $content .= "}";

            file_put_contents($fileName, $content);
            $bar->advance();
        }

        $bar->finish();

        $this->comment("\n\nFiles created with success!\n");
    }

    private function camelCase($text, $firstLetter = true) {
        $sep = array("-", "_");
        $text = str_replace($sep, " ", $text);
        $result = '';
        $result = ucwords($text);
        if (!$firstLetter) {
            $result = lcfirst($result);
        }
        return (str_replace(" ", "", $result));
    }

    private function getManyToManyAnotation($tableName, $referenceTable, $refereceCol, $db, $database, $namespace) {

        $query = "SELECT TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
            FROM KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = '$database' 
            AND TABLE_NAME = '$tableName'
            AND REFERENCED_TABLE_NAME != '$referenceTable'
            AND REFERENCED_COLUMN_NAME IS NOT NULL";

        if ($stmt = $db->prepare($query)) {
            if ($stmt->execute()) {
                while ($i = $stmt->fetch()) {
                    $tableName = $i['TABLE_NAME'];
                    $colName = $i['COLUMN_NAME'];
                    $otherTable = $i['REFERENCED_TABLE_NAME'];
                    $otherCol = $i['REFERENCED_COLUMN_NAME'];
                    $field = $this->camelCase($otherTable, false);
                    $field .= substr($field, -1) != "s" ? "s" : '';

                    return ["anotation" => "\t* @manyToMany(lazy)\n\t* @joinColumn(" . $refereceCol . ")\n\t* @inverseJoinColumn(" . $colName . ")\n\t* @itemClass(" . ($namespace != '' ? "$namespace\\" : "") . $this->camelCase($otherTable) . ")\n\t* @joinTable(" . $tableName . ")", "field" => $field];
                }
            }
        }
        $this->error("\nError to get Many to Many Anotation for $tableName!\n");
        exit;
    }

    private function createGetsAndSets($field) {
        $getsSets = "\tpublic function get" . $this->camelCase($field) . "() {\n";
        $getsSets .= "\t\treturn \$this->" . $this->camelCase($field, false) . ";\n\t}\n\n";
        $getsSets .= "\tpublic function set" . $this->camelCase($field) . "($" . $this->camelCase($field, false) . ") {\n";
        $getsSets .= "\t\t\$this->" . $this->camelCase($field, false) . " = $" . $this->camelCase($field, false) . ";\n\t}\n";
        return $getsSets;
    }

}
