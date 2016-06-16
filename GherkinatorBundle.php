<?php
/**
 * Created by salma BHA
 * Date: 13/06/16 16:28
 * Class GherkinatorBundle
 */

namespace Open\GherkinatorBundle;

use Open\GherkinatorBundle\Command\GherkinatorGenerateCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GherkinatorBundle extends Bundle
{
    public function registerCommands(Application $application)
    {
        // Use the default logic when the ORM is available.
        // This avoids listing all ORM commands by hand.
        if (class_exists('Doctrine\\ORM\\Version')) {
            parent::registerCommands($application);

            return;
        }

        // Register only the DBAL commands if the ORM is not available.
        $application->add(new GherkinatorGenerateCommand());
    }
}
