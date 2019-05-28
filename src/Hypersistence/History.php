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
     * @column(author_table)
     */
    private $authorTable;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getReferenceId() {
        return $this->referenceId;
    }

    public function getReferenceTable() {
        return $this->referenceTable;
    }

    public function setReferenceId($referenceId) {
        $this->referenceId = $referenceId;
    }

    public function setReferenceTable($referenceTable) {
        $this->referenceTable = $referenceTable;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function setAuthor($author) {
        $this->author = $author;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getDate() {
        return $this->date;
    }

    public function setDate($date) {
        $this->date = $date;
    }
    
    function getAuthorTable() {
        return $this->authorTable;
    }

    function setAuthorTable($authorTable) {
        $this->authorTable = $authorTable;
    }

}
