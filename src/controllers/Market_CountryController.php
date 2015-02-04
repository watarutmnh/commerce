<?php
namespace Craft;

class Market_CountryController extends Market_BaseController
{
	/**
	 * @throws HttpException
	 */
	public function actionIndex()
	{
		$countries = craft()->market_country->getAll();
		$this->renderTemplate('market/settings/countries/index', compact('countries'));
	}

	/**
	 * Create/Edit Country
	 *
	 * @param array $variables
	 *
	 * @throws HttpException
	 */
	public function actionEdit(array $variables = array())
	{
		if (empty($variables['country'])) {
			if (!empty($variables['id'])) {
				$id                   = $variables['id'];
				$variables['country'] = craft()->market_country->getById($id);

				if (!$variables['country']) {
					throw new HttpException(404);
				}
			} else {
				$variables['country'] = new Market_CountryModel();
			};
		}

		if (!empty($variables['id'])) {
			$variables['title'] = $variables['country']->name;
		} else {
			$variables['title'] = Craft::t('Create a Country');
		}

		$this->renderTemplate('market/settings/countries/_edit', $variables);
	}

	/**
	 * @throws HttpException
	 */
	public function actionSave()
	{
		$this->requirePostRequest();

		$country = new Market_CountryModel();

		// Shared attributes
		$country->id            = craft()->request->getPost('countryId');
		$country->name          = craft()->request->getPost('name');
		$country->iso           = craft()->request->getPost('iso');
		$country->stateRequired = craft()->request->getPost('stateRequired');

		// Save it
		if (craft()->market_country->save($country)) {
			craft()->userSession->setNotice(Craft::t('Country saved.'));
			$this->redirectToPostedUrl($country);
		} else {
			craft()->userSession->setError(Craft::t('Couldn’t save country.'));
		}

		// Send the model back to the template
		craft()->urlManager->setRouteVariables(array(
			'country' => $country
		));
	}

	/**
	 * @throws HttpException
	 */
	public function actionDelete()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$id = craft()->request->getRequiredPost('id');

		craft()->market_country->deleteById($id);
		$this->returnJson(array('success' => true));
	}

}