<?php

namespace SalesForceDataManager;

class SchemaManager
{
    public $client;
    public function __construct($soapClient)
    {
        $this->client = $soapClient;
    }

    public function getSfObjects()
    {
        $forceObjectsFiltered = [];
        $forceObjects = [];
        foreach ($this->client->describeGlobal()->sobjects as $sobject) {
            $forceObjects[$sobject->name] = $sobject;
            if ($sobject->layoutable === true || $sobject->searchable === true) {
                $forceObjectsFiltered[$sobject->name] = $sobject;
            }
        }
        $sobjectNames = array_chunk(array_keys($forceObjectsFiltered), 20);
        $sobjectDescribe = [];
        foreach ($sobjectNames as $sobjectNameChunk) {
            foreach ($this->client->describeSObjects($sobjectNameChunk) as $sobjectfull) {
                $sobjectDescribe[$sobjectfull->name] = $sobjectfull;
            }
        }
        return $sobjectDescribe;
    }


    public function SalesForceObjectToDoctrine($sfObject)
    {
        $nullable= "(nullable=true)";
        foreach ($sfObject->fields as $obj) {
            $objs[] = $obj->name;
        }
        foreach ($sfObject->fields as $item) {
            switch ($item->type) {
                case "location":
                    //bulkapi error with location fields
                    continue;
                    break;
                case "double":
                    $fieldType = "float". $nullable;
                    break;
                case "boolean":
                case "date":
                case "datetime":
                    $fieldType = "datetime". $nullable;
                    break;
                case "textarea":
                    $fieldType = "text".$nullable;
                    break;
                case "id":
                    //$fieldType = "guid";
                    $fieldType = '';
                    break;
                default:
                    if ($item->length < 256) {
                        $fieldType = "string(length={$item->length} nullable=true)";
                    } else {
                        $fieldType = "text".$nullable;
                    }

            }

            if (!empty($fieldType)) {
                $convertedFields[$item->name] = $item->name . ":" . $fieldType;
            }

        }
        return 'php app/console generate:doctrine:entity --no-interaction --fields='.implode(" ", $convertedFields);
    }
}
