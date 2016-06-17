<?php
use Behat\MinkExtension\Context\MinkContext;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class WebContext extends MinkContext{
/**
* Initializes context.
*
* Every scenario gets its own context instance.
* You can also pass arbitrary arguments to the
* context constructor through behat.yml.
*/

    private $tLabels;
    private $tVariables;

    public function __construct(){
        $this->tLabels = array();
        $this->tVariables = array();
    }

    /**
    * @When I wait for :arg1 seconds
    */
    public function iWaitForSeconds($arg1){
        $this->getSession()->wait($arg1 * 1000);
    }


    /**
    * @When I click on :target :type
    *
    * Cliquer sur l'élement $target
    */
    public function iClickOn($target, $type){
        if($type == 'css'){
            $this->iClickTheElementWithCssSelector($target);
        }
        elseif($type == 'xpath'){
            $this->iClickOnTheElementWithXPath($target);
        }
        elseif($type == 'id'){
            $this->iClickTheElementWithId($target);
        }
        elseif($type == 'link'){
            $this->iClickTheElementWithLink($target);
        }
        else{
            throw new \Exception(sprintf("The '%s' is not managed", $type));
        }
    }


    public function iClickTheElementWithCssSelector($css_selector) {
        // runs the actual query and returns the element
        $element = $this->getSession()->getPage()->find("css", $css_selector);
        // errors must not pass silently
        if (empty($element)) {
            throw new \Exception(sprintf("The page '%s' does not contain the css selector '%s'", $this->getSession()->getCurrentUrl(), $css_selector));
        }
        /*$javascript = "$('".$css_selector."')[0].click();";
        $this->getSession()->executeScript($javascript);*/
        // ok, let's click on it

        $element->click();
    }


    public function iClickTheElementWithId($id) {
        // runs the actual query and returns the element
        $element = $this->getSession()->getPage()->findById($id);
        // errors must not pass silently
        if (empty($element)) {
            throw new \Exception(sprintf("The page '%s' does not contain the css selector '%s'", $this->getSession()->getCurrentUrl(), $id));
        }
        // ok, let's click on it
        $element->click();
    }


    public function iClickOnTheElementWithXPath($xpath){
        // get the mink session
        $session = $this->getSession();
        // runs the actual query and returns the element
        $element = $session->getPage()->find(
        'xpath',
        $session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
        );
        // errors must not pass silently
        if (null == $element) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));
        }
        // ok, let's click on it
        $element->click();
    }

    public function iClickTheElementWithLink($link) {
        // runs the actual query and returns the element
        $element = $this->getSession()->getPage()->findLink($link);
        // errors must not pass silently
        if (empty($element)) {
            throw new \Exception(sprintf("The page '%s' does not contain the link '%s'", $this->getSession()->getCurrentUrl(), $link));
        }
        // ok, let's click on it
        $element->click();
    }


    /**
    * @Then I should see :target :type
    *
    * Vérifier que l'élement $target est présente
    */
    public function iShouldSee($target, $type){
        if($type == 'css'){
            $element = $this->getSession()->getPage()->find("css", $target);
        }
        elseif($type == 'xpath'){
            $session = $this->getSession();
            $element = $this->getSession()->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $target)
            );
        }
        elseif($type == 'link') {
            $element = $this->getSession()->getPage()->findLink($target);
        }
        if (empty($element)) {
            throw new \Exception(sprintf("The page '%s' does not contain '%s'", $this->getSession()->getCurrentUrl(), $target));
        }
    }


    /**
    * @Then I should see :target :type with :value
    *
    * Vérifier que l'élement $target possède la valeur $value
    */
    public function iShouldSeeWith($target, $type, $value) {
        $element = null;
        $valeur = null;
        if($type == 'css') {
            $element = $this->getSession()->getPage()->find($type, $target);
            $valeur = $element->getHtml();
        }
        elseif($type == 'xpath') {
            $session = $this->getSession();
            $element = $this->getSession()->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $target)
            );
            $valeur = $element->getHtml();
        }
        elseif($type == 'id') {
            $element = $this->getSession()->getPage()->findById($target);
            $valeur = $element->getValue();
        }
        elseif($type == 'link') {
            $element = $this->getSession()->getPage()->findLink($target);
            $valeur = $element->getValue();
        }
        if(empty($element) || trim($valeur)!= $value) {
            throw new \Exception(sprintf("The page '%s' does not contain '%s'", $this->getSession()->getCurrentUrl(), $target));
        }
    }

    /**
    * @Then I should see :target with value :value
    *
    * Vérifier que l'élement $target possède la valeur $value
    */
    public function iShouldSeeWithValue($target, $value){
        $element = null;
        $element = $this->getSession()->getPage()->find('css', $target);
        if(empty($element)){
            $session = $this->getSession();
            $element = $this->getSession()->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $target)
            );
        }
        if(empty($element)){
            $element = $this->getSession()->getPage()->findById($target);
        }
        if(empty($element)){
            $element = $this->getSession()->getPage()->findLink($target);
        }
        if(empty($element) || $element->getValue() != $value) {
            throw new \Exception(sprintf("The page '%s' does not contain '%s'", $this->getSession()->getCurrentUrl(), $target));
        }
    }

    /**
    * @Then I should not see the element :arg1 :arg2
    */
    public function iShouldNotSeeTheElement($arg1, $arg2){
        $this->iShouldNotSee($arg1, $arg2);
    }


    /**
    * @Then I should not see :type :target
    *
    * Vérifier que l'élement $target n'est pas présent
    */
    public function iShouldNotSee($type, $target) {
        $element = null;
        if($type == 'css') {
            $element = $this->getSession()->getPage()->find($type, $target);
        }
        elseif($type == 'xpath') {
            $session = $this->getSession();
            $element = $this->getSession()->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $target)
            );
        }
        elseif($type == 'id') {
            $element = $this->getSession()->getPage()->findById($target);
        }
        elseif($type == 'link') {
            $element = $this->getSession()->getPage()->findLink($target);
        }
        if($element) {
            throw new \Exception(sprintf("The page '%s' should not contain the css selector '%s'", $this->getSession()->getCurrentUrl(), $target));
        }
    }

    /**
    * @Then I should not see :type :target with value :value
    *
    * Vérifier que l'élement $target n'est pas présent
    */
    public function iShouldNotSeeWithValue($type, $target, $value) {
        $element = null;
        if($type == 'css') {
            $element = $this->getSession()->getPage()->find($type, $target);
        }
        elseif($type == 'xpath') {
            $session = $this->getSession();
            $element = $this->getSession()->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $target)
            );
        }
        elseif($type == 'id') {
            $element = $this->getSession()->getPage()->findById($target);
        }
        if(empty($element) || $element->getValue() == $value) {
            throw new \Exception(sprintf("The page '%s' should not contain the css selector '%s'", $this->getSession()->getCurrentUrl(), $target));
        }
    }


    /**
    * @When I store :target :type into :variable
    *
    */
    public function iStoreInto($target, $type, $variable) {
        if($type == 'css') {
            $element = $this->getSession()->getPage()->find("css", $target);
        }
        elseif($type == 'xpath') {
            $element = $this->getSession()->getPage()->find("xpath", $target);
        }
        if (!empty($element)) {
            $this->tVariables[$variable] = $element->getValue();
        }
        else{
            $this->tVariables[$variable] = false;
        }
    }


    /**
    * @Then I go to :label if :condition
    */
    public function iGoToIf($label, $condition) {
        $expression = $this->readExpression($condition);
        $language = new ExpressionLanguage();
        $language->evaluate($expression);
    }

    public function readExpression($target) {
        $expression =null;
        $tVariables = $this->tVariables;
        if(is_array($tVariables)){
            foreach($tVariables as $variable => $valeur){
                if(strpos($variable, $target)){
                    $expression = str_replace($variable, $valeur, $target);
                }
            }
        }
        echo $expression;
        return $expression;
    }


    /**
    * @Given I am on label :label
    */
    public function iAmOnLabel($label) {
        $this->tLabels[$label] = $this->getSession()->getCurrentUrl();
    }

    /**
    * @When I fill in :target :type with :value
    */
    public function iFillInWith($target, $type, $value) {
        $element = null;
        if($type == 'css') {
            $element = $this->getSession()->getPage()->find($type, $target);
        }
        elseif($type == 'xpath') {
            $session = $this->getSession();
            $element = $this->getSession()->getPage()->find(
            'xpath',
            $session->getSelectorsHandler()->selectorToXpath('xpath', $target)
            );
        }
        elseif($type == 'id') {
            $element = $this->getSession()->getPage()->findById($target);
            //var_dump($element->getHtml());
        }
        elseif($type == 'link') {
            $element = $this->getSession()->getPage()->findLink($target);
        }
        if (empty($element)) {
            throw new \Exception(sprintf("The page '%s' contains the css selector '%s'", $this->getSession()->getCurrentUrl(), $target));
        }
        else{
            $element->fillField($target, $value);
        }
    }

    /**
     * @Then I take a screen shot with the prefix :prefix
     */
    public function iTakeAScreenShotWithPrefix($prefix) {
        /*$container = new \Symfony\Component\DependencyInjection\Container();
        $screen_shots = $container->getParameter('screen_shots_path');
        $screen_shots = $container->getParameter('screen_shots_path');*/
        $screen_shots = 'screen_shots';
        $file = $prefix.'_'.date('Ymd_His').'.png';
        $this->saveScreenshot($file, $screen_shots);
    }


    /**
    * @When I select :target :type with :value
    */
    public function iSelectWith($target, $type, $value) {
        $element = null;
        if($type == 'id'){
            $element = $this->getSession()->getPage()->findById($target);
        }
        if($type == 'named') {
            $element = $this->getSession()->getPage()->hasSelect($target);
        }
        if (empty($element)) {
            throw new \Exception(sprintf("The page '%s' does not contain the css selector '%s'", $this->getSession()->getCurrentUrl(), $target));
        }
        else{
            $element->selectOption($value);
        }
    }
}
?>