<?php
/**
 * @author: Open
 */
namespace Open\GherkinatorBundle\Command;

use Open\GherkinatorBundle\GherkinatorBundle;
use Open\GherkinatorBundle\Services\Converter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class GherkinatorGenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gherkinator:feature:generate')
            ->setAliases(array('gherkinator:feature:generate'))
            ->setDescription('Command to generate features from an IDE Selenium tests directory')
            ->setDefinition(array(
                new InputArgument('folder_path', InputArgument::REQUIRED, 'The folder path')
            ))
            ->setHelp(
<<<EOT
        The <info>gherkinator:feature:generate</info> command creates features
        
        <info>php app/console gherkinator:feature:generate</info>
        
        This interactive shell will ask you for the folder path.
        
        You can alternatively specify the folder as an argument:

        <info>php app/console gherkinator:feature:generate <folder_path></info>
        
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $folder  = $input->getArgument('folder_path');
        if(is_dir($folder)){
            $converter = new Converter($this->getContainer());
            $name_log_file = $converter->execute($folder);
            $message = "\nTo know more details you can see :\n\t".$this->getContainer()->getParameter('log_path')."$name_log_file\n\n";
            $message.= "\nAll the Gherkin steps definitions are in the  :\n\t" .$this->getContainer()->getParameter('features_path')."bootstrap/WebContext.php\n\n";
            $output->writeln($message);
        }
        else{
            throw  new \Exception('Check your path');
        }

    }


    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$this->getHelperSet()->has('question')) {
            $this->legacyInteract($input, $output);

            return;
        }

        $questions = array();

        if (!$input->getArgument('folder_path')) {
            $question = new Question('Please choose a folder path: ');
            //$question->setAutocompleterValues()
            $question->setValidator(function ($sender) {
                if (empty($sender)) {
                    throw new \Exception('folder path can not be empty');
                }

                return $sender;
            });
            $questions['folder_path'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }

    }



    private function legacyInteract(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('folder_path')) {
            $folder = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a folder path: ',
                function ($sender) {
                    if (empty($sender)) {
                        throw new \Exception('folder can not be empty');
                    }

                    return $sender;
                }
            );
            $input->setArgument('folder_path', $folder);
        }

    }

}
