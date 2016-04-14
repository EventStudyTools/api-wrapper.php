<?php
namespace ApplicationInput;

use stdClass;

class CataApiInput extends AbstractApplicationInput implements ApplicationInputInterface {
    /**
     * @param array $parameters
     */
    public function __construct($parameters) {

        /*
         * Task
         */
        $this->set('task', $parameters, 'email');
        $this->setWithDefault('task', $parameters, 'locale', 'en');

        /*
         * Application
         */
        $this->setWithDefault('application', $parameters, 'key', 'cata');

        $text = new stdClass();
        $text->key = 'text_data';
        $text->type = $parameters['datasources']['text_data'];

        $keywords = new stdClass();
        $keywords->key = 'keywords_data';
        $keywords->type = $parameters['datasources']['keywords_data'];

        $this->application->data_sources = array(
            $text, $keywords
        );
    }

    public function toJson() {
        return json_encode($this);
    }
} 