[?php

/**
 * <?php echo $this->getModuleName() ?> actions. REST API for the model "<?php echo $this->getModelClass() ?>"
 *
 * @package    ##PROJECT_NAME##
 * @subpackage <?php echo $this->getModuleName()."\n" ?>
 * @author     ##AUTHOR_NAME##
 *
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z xavier $
 */
class <?php echo $this->getGeneratedModuleName() ?>Actions extends <?php echo $this->getActionsBaseClass() ?>

{
  public $model = '<?php echo $this->getModelClass() ?>';

<?php include dirname(__FILE__).'/../../parts/apiAuthFunctions.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/cleanupParameters.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/configureFields.php' ?>

<?php include dirname(__FILE__).'/../../parts/createAction.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/createObject.php' ?>

<?php include dirname(__FILE__).'/../../parts/deleteAction.php' ?>

<?php include dirname(__FILE__).'/../../parts/doSave.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/getCreatePostValidators.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/getCreateValidators.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/getFormat.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/getIndexPostValidators.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/getIndexValidators.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/getSerializer.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/getUpdatePostValidators.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/getUpdateValidators.php' ?>

<?php include dirname(__FILE__).'/../../parts/indexAction.php' ?>

<?php include dirname(__FILE__).'/../../parts/parsePayload.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/postValidate.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/query.php' ?>
<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/queryAdditionnal.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/queryExecute.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/queryFetchOne.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/setFieldVisibility.php' ?>

<?php include dirname(__FILE__).'/../../parts/showAction.php' ?>

<?php include dirname(__FILE__).'/../../parts/updateAction.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/updateObjectFromRequest.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/validate.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/validateCreate.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/validateIndex.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/validateShow.php' ?>

<?php include sfConfig::get('sf_plugins_dir').'/sfDoctrineRestGeneratorPlugin/data/generator/sfDoctrineRestGenerator/default/parts/validateUpdate.php' ?>
}
