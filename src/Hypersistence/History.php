<?php

namespace Hypersistence;

/**
 * @table(history)
 */
class History extends Hypersistence {

    /**
     * @primaryKey
     * @column(id)
     */
    private $id;

    /**
     * @column(reference_id)
     */
    private $referenceId;

    /**
     * @column(reference_table)
     * @searchMode()
     */
    private $referenceTable;

    /**
     * @column(author_id)
     */
    private $author;

    /**
     * @column(description)
     */
    private $description;

    /**
     * @column(date)
     */
    private $date;

    /**
     * @column(author_class)
     */
    private $authorClass;

    public function getId() {
        return $this->id;
    }

    public function getReferenceId() {
        return $this->referenceId;
    }

    public function getReferenceTable() {
        return $this->referenceTable;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getDate() {
        return $this->date;
    }

    public function getAuthorClass() {
        return $this->authorClass;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setReferenceId($referenceId) {
        $this->referenceId = $referenceId;
    }

    public function setReferenceTable($referenceTable) {
        $this->referenceTable = $referenceTable;
    }

    public function setAuthor($author) {
        $this->author = $author;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setDate($date) {
        $this->date = $date;
    }

    public function setAuthorClass($authorClass) {
        $this->authorClass = $authorClass;
    }
}
