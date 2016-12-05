<?php
namespace Zenril\PHPGenerator\Commands;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Doctrine\ORM\Tools\Console\MetadataFilter;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;
use Zenril\PHPGenerator\TemplateClassWriter;
use Symfony\Component\Console\Question\ChoiceQuestion;

class CreateDoctrineEntityCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
        ->setName('app:create:entity')
        ->setDescription('Greet someone')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Who do you want to greet?'
            )
            ->addOption(
                'yell',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will yell in uppercase letters'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {    
       //
        $hasUniqueId = false;
        $nameMap = array();
        $helper = new QuestionHelper();
        $classname =  $helper->ask($input, $output, new Question("<question>Please enter class name:</question>\n> ", null));

        $members = array();

        $i = 0;

        do {
            $member = array();
            if($i > 0){
                
                $output->writeln("","");
                $member['name'] =  $helper->ask($input, $output, new Question("<question>Enter another member name, else leave blank:</question>\n> ", null));
                if( empty($member['name']) ){
                    break;
                }
            } else {
                $output->writeln("","");
                $member['name'] =  $helper->ask($input, $output, new Question("<question>Please enter member name:</question>\n> ", null));
            }
            if($i > 1000){
                throw new \Exception(
                    'Quiting.. I seem to be in a crazy loop. I\'ll still try to generate what you have entered so far.'
                );
                break;
            }
            $i++;
            
            //parse is id
            $output->writeln("","");
            $id_question = new ChoiceQuestion(
                '<question>Is `'.$member['name'].'` an ID (defaults to false):</question>',
                array('false', 'true'),
                0
            );

            $id_answer = $helper->ask($input, $output,  $id_question);
            $member['id'] = (bool) filter_var($id_answer, FILTER_VALIDATE_BOOLEAN);

            if($member['id']){
                $hasUniqueId = true;
            }

            //parse column
            $member['column'] = array();

            //parse type
            $output->writeln("","");
            $type_question = new ChoiceQuestion(
                '<question>please select type for `'.$member['name'].'` (defaults to string):</question>',
                array(
                    "string: Type that maps a SQL VARCHAR to a PHP string.",
                    "integer: Type that maps a SQL INT to a PHP integer.",
                    "smallint: Type that maps a database SMALLINT to a PHP integer.",
                    "bigint: Type that maps a database BIGINT to a PHP string.",
                    "boolean: Type that maps a SQL boolean or equivalent (TINYINT) to a PHP boolean.",
                    "decimal: Type that maps a SQL DECIMAL to a PHP string.",
                    "date: Type that maps a SQL DATETIME to a PHP DateTime object.",
                    "time: Type that maps a SQL TIME to a PHP DateTime object.",
                    "datetime: Type that maps a SQL DATETIME/TIMESTAMP to a PHP DateTime object.",
                    "datetimetz: Type that maps a SQL DATETIME/TIMESTAMP to a PHP DateTime object with timezone.",
                    "text: Type that maps a SQL CLOB to a PHP string.",
                    "object: Type that maps a SQL CLOB to a PHP object using serialize() and unserialize()",
                    "array: Type that maps a SQL CLOB to a PHP array using serialize() and unserialize()",
                    "simple_array: Type that maps a SQL CLOB to a PHP array using implode() and explode(), with a comma as delimiter. IMPORTANT Only use this type if you are sure that your values cannot contain a ”,”.",
                    "json_array: Type that maps a SQL CLOB to a PHP array using json_encode() and json_decode()",
                    "float: Type that maps a SQL Float (Double Precision) to a PHP double. IMPORTANT: Works only with locale settings that use decimal points as separator.",
                    "guid: Type that maps a database GUID/UUID to a PHP string. Defaults to varchar but uses a specific type if the platform supports it.",
                    "blob: Type that maps a SQL BLOB to a PHP resource stream"
                ),
                0
            );

            $type_answer = $helper->ask($input, $output,  $type_question);
            $member['column']['type'] = trim( explode(":", $type_answer, 2)[0]);

            if($member['column']['type'] == 'string'){
                //find varchar length
                $output->writeln("","");
                $string_type_length_question = new Question("<question>Please enter length of `".$member['name']."` string 0-255 (defaults to 255):</question> \n>", "255");
                $string_type_length_question->setValidator(function ($answer) {

                    if (!is_numeric($answer)){
                        throw new \Exception(
                            'This doesnt look like a number'
                        );
                    }    
                    if(intval($answer) > 256 || intval($answer) < 0) {
                        throw new \Exception(
                            '`string` length needs to be between 0-255'
                        );
                    }

                    return $answer;
                });

                $string_type_length_question->setMaxAttempts(5);

                $member['column']['length'] = $helper->ask($input, $output, $string_type_length_question);       
                 $output->writeln($member['column']['length'],"");        
            }
            
            //parsenullable
            $output->writeln("","");
            $nullable_question = new ChoiceQuestion(
                '<question>Is `'.$member['name'].'` nullable (defaults to true):</question>',
                array('false', 'true'),
                1
            );

            $nullable_answer = $helper->ask($input, $output,  $nullable_question);
            $member['column']['nullable'] =  (bool) filter_var($nullable_answer, FILTER_VALIDATE_BOOLEAN);
            
            $members[] = $member;
            $nameMap[strtolower($member['name'])] = $member;
        } while (true);

        if(!$hasUniqueId){
            $name = "id";
            
            if(isset($nameMap["id"])){
                $name = $classname . "Id";
            }

            if(isset($nameMap[strtolower($name)])){
                $name = $classname ."_". rand ( 3 , 5) . "_Id" . ;
            }

            array_unshift( $members, array(
                "name" => $name,

                
                


            ));
        }

        $output->writeln("","");
        $create_question = new ChoiceQuestion(
            "<question> Are you sure you want to create the Entity $classname (defaults to true):</question>",
            array('false', 'true'),
            1
        );

        $create_answer = $helper->ask($input, $output,  $create_question);
        $create_answer =  (bool) filter_var($create_answer, FILTER_VALIDATE_BOOLEAN);
        if($create_answer){
                $PHPG = new TemplateClassWriter("app/Entities");
                $PHPG->classData($classname, array(
                    'members' => $members
                ));

                $PHPG->write();
        }

        $output->writeln($classname);
    }
}
