<?php

class TestimonialRest extends MongoRest {
  /**
   * Get testimonials
   *
   * @url GET /testimonials
   **/
  public function getTestimonials() {
    return $this->getCollection("testimonials");
  }

  /**
   * Get a single testimonial
   *
   * @url GET /testimonials/$id
   **/
  public function getTestimonial($id) {
    return $this->getSingle("testimonials", array('_id' => new MongoId($id)));
  }

  /**
   * Save a single testimonial
   *
   * @url PUT /testimonials/$id
   **/
  public function saveTestimonial($id, $__data = array()) {
    $res = $this->updateItem("testimonials", array('_id' => new MongoId($id)), $__data);
    if ($res) {
      return $this->getTestimonial($id);
    } else {
      throw new REST\Exception('500');
    }
  }

  /**
   * Delete a single testimonial
   *
   * @url DELETE /testimonials/$id
   **/
  public function deleteTestimonial($id, $__data = array()) {
    $res = $this->deleteItem("testimonials", array('_id' => new MongoId($id)));
    if ($res) {
      return true;
    } else {
      throw new REST\Exception('500');
    }
  }
};

?>
