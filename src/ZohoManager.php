<?php

namespace ZCRM;

use ZCRM\Exceptions\ZCRMException;
use ZCRM\Support\ZCRMSearchBuilder;

class ZohoManager
{
    protected ?string $crmName = null;
    protected ?string $moduleName = null;

    public function use(string $crmName): self
    {
        $this->crmName = $crmName;
        return $this;
    }

    public function useModule(string $moduleName): self
    {
        if (!$this->crmName) {
            $defaultCrm = (new \ZCRM\Support\ClientManager())->getConnection();
            if (!$defaultCrm) {
                throw new ZCRMException("Aucune connexion CRM trouvée.");
            }
            $this->crmName = $defaultCrm['name'];
        }

        $this->moduleName = $moduleName;
        return $this;
    }

    protected function getHandler(): ModuleHandler
    {
        if (!$this->moduleName) {
            throw new ZCRMException("Aucun module Zoho sélectionné via useModule().");
        }

        return new ModuleHandler($this->crmName, $this->moduleName);
    }

    // Appels API exposés publiquement
    public function getRecords(array $options = []): array
    {
        return $this->getHandler()->getRecords($options);
    }

    public function getRecord(string $id): array
    {
        return $this->getHandler()->getRecord($id);
    }

    public function createRecord(array $data): array
    {
        return $this->getHandler()->createRecord($data);
    }

    public function updateRecord(string $id, array $data): array
    {
        return $this->getHandler()->updateRecord($id, $data);
    }

    public function getAllRecords(array $options = []): array
    {
        return $this->getHandler()->getAllRecords($options);
    }

    public function deleteRecord(string $id): bool
    {
        return $this->getHandler()->deleteRecord($id);
    }

    public function uploadFile(string $recordId, string $filePath): array
    {
        return $this->getHandler()->uploadFile($recordId, $filePath);
    }

    public function findByCriteria(string|ZCRMSearchBuilder $criteria): array
    {
        if ($criteria instanceof ZCRMSearchBuilder) {
            $criteria = $criteria->build();
        }

        return $this->getHandler()->findByCriteria($criteria);
    }
}
