<?php

namespace App\Service;

use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\TagsCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\TagModel;

class LeadService
{
    private $clientApi;

    public function __construct(AmoCRMApiClient $clientApi)
    {
        $this->clientApi = $clientApi;
    }

    public function create(array $leadData)
    {
        $leadsCollection = $this->buildLeadData($leadData);

        try {
            return $this->clientApi->leads()->addComplex($leadsCollection);
        } catch (AmoCRMApiException $e) {
            throw $e;
        }
    }

    private function buildLeadData(array $leadData) : LeadsCollection
    {
        $leadsCollection = new LeadsCollection();

        $leadModel = new LeadModel();
        $leadModel->setName($leadData['name']);
        $leadModel->setTags((new TagsCollection())->add(
            (new TagModel())->setName($leadData['tag'])
        ));

        $contactModel = new ContactModel();
        $contactModel->setFirstName($leadData['contact']['first_name']);
        $contactModel->setLastName($leadData['contact']['last_name']);

        $contactModel->setCustomFieldsValues(
            (new CustomFieldsValuesCollection())->add(
                (new MultitextCustomFieldValuesModel())->setFieldCode('PHONE')->setValues(
                    (new MultitextCustomFieldValueCollection())->add(
                        (new MultitextCustomFieldValueModel())->setValue($leadData['contact']['phone'])
                    )
                )
            )
        );

        $leadModel->setContacts((new ContactsCollection())->add($contactModel));
        $leadModel->setCompany((new CompanyModel())->setName($leadData['company']['name']));

        $leadsCollection->add($leadModel);
        return $leadsCollection;
    }
}