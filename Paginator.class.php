<?php

  /* TODO
   *  - refactor setting of preferences
   */

  class Paginator {

    private $total_items;
    private $items_per_page;           // 15
    private $links_per_page;           // 7
    private $page_count;
    private $etc_text;                 // '...'
    private $current_page;
    private $show_previous_next_links; // true
    private $previous_link_text;       // 'Previous'
    private $next_link_text;           // 'Next'
    private $show_first_last_links;    // true
    private $first_link_text;          // 'First'
    private $last_link_text;           // 'Last'

    function Paginator($items_count = null, $preferences = array()) {
      set_exception_handler(array('Paginator', 'exception_handler'));
      $this->validate_items_count($items_count);
      $this->total_items = $items_count;
      $this->items_per_page = (isset($preferences['items_per_page'])) ? $preferences['items_per_page'] : 15;
      if (isset($preferences['links_per_page'])) {
        $this->determine_links_per_page($preferences['links_per_page']);
      } else {
        $this->links_per_page = 7;
      }
      if ($this->links_per_page % 2 == 0) {
        $this->links_per_page += 1;
      }
      $this->page_count = ceil($this->total_items / $this->items_per_page);
      $this->etc_text = (isset($preferences['etc_text'])) ? $preferences['etc_text'] : '...';
      $this->show_previous_next_links = (isset($preferences['show_previous_next_links'])) ? $preferences['show_previous_next_links'] : true;
      $this->previous_link_text = (isset($preferences['previous_link_text'])) ? $preferences['previous_link_text'] : 'Previous';
      $this->next_link_text = (isset($preferences['next_link_text'])) ? $preferences['next_link_text'] : 'Next';
      $this->show_first_last_links = (isset($preferences['show_first_last_links'])) ? $preferences['show_first_last_links'] : true;
      $this->first_link_text = (isset($preferences['first_link_text'])) ? $preferences['first_link_text'] : 'First';
      $this->last_link_text = (isset($preferences['last_link_text'])) ? $preferences['last_link_text'] : 'Last';
      $this->get_current_page();
    }

    function paginate() {
      $this->return = '<div class="pagination">';
      $this->previous_link();
      $this->first_link();
      $range = $this->get_links_range();
      $start = $range[0];
      $end = $range[1];
      if ($start > 1) {
        $this->return .= '<span class="paginatorEtc">' . $this->etc_text . '</span>';
      }
      for ($i = $start; $i <= $end; $i++) {
        if ($i == $this->current_page) {
          $this->return .= '<em>' . $i . '</em>';
        } else {
          $this->return .= '<a href="' . $_SERVER['PHP_SELF'] . '?page=' . $i . '">' . $i . '</a>';
        }        
      }
      if ($end < $this->page_count) {
        $this->return .= '<span class="paginatorEtc">' . $this->etc_text . '</span>';
      }
      $this->last_link();
      $this->next_link();
      $this->return .= '</div>';
      return $this->return;
    }

    private function validate_items_count($items_count) {
      if (!isset($items_count) or isset($items_count) and is_array($items_count)) {
        throw new Exception('Total number of items is not given.');
      }
      if (!is_numeric($items_count)) {
        throw new Exception('Given number of items is not an integer.');
      }
    }

    private function determine_links_per_page($links_per_page) {
      if (!isset($links_per_page) or $links_per_page < 2) {
        $this->links_per_page = 7;
      } else {
        $this->links_per_page = $links_per_page;
      }
    }

    private function get_current_page() {
      if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] >= 1) {
        $this->current_page = $_GET['page'];
        if ($_GET['page'] < 1) {
          $this->current_page = 1;
        } else if ($_GET['page'] > $this->page_count) {
          $this->current_page = $this->page_count;
        }
      } else {
        $this->current_page = 1;
      }
    }

    private function previous_link() {
      if ($this->show_previous_next_links) {
        if ($this->current_page == 1) {
          $this->return .= '<span class="paginatorPrevious disabled">' . $this->previous_link_text . '</span>';
        } else {
          $this->return .= '<a href="' . $_SERVER['PHP_SELF'] . '?page=' . ($this->current_page - 1) . '" class="paginatorPrevious">' . $this->previous_link_text . '</a>';
        }
      }
    }

    private function next_link() {
      if ($this->show_previous_next_links) {
        if ($this->current_page == $this->page_count) {
          $this->return .= '<span class="paginatorNext disabled">' . $this->next_link_text . '</span>';
        } else {
          $this->return .= '<a href="' . $_SERVER['PHP_SELF'] . '?page=' . ($this->current_page + 1) . '" class="paginatorNext">' . $this->next_link_text . '</a>';
        }
      }
    }

    private function first_link() {
      if ($this->show_first_last_links) {
        if ($this->current_page == 1) {
          $this->return .= '<span class="paginatorFirst disabled">' . $this->first_link_text . '</span>';
        } else {
          $this->return .= '<a href="' . $_SERVER['PHP_SELF'] . '?page=1" class="paginatorFirst">' . $this->first_link_text . '</a>';
        }
      }
    }

    private function last_link() {
      if ($this->show_first_last_links) {
        if ($this->current_page == $this->page_count) {
          $this->return .= '<span class="paginatorLast disabled">' . $this->last_link_text . '</span>';
        } else {
          $this->return .= '<a href="' . $_SERVER['PHP_SELF'] . '?page=' . $this->page_count . '" class="paginatorLast">' . $this->last_link_text . '</a>';
        }
      }
    }

    private function get_links_range() {
      $start = $this->current_page - floor($this->links_per_page / 2);
      $end = $this->current_page + floor($this->links_per_page / 2);
      if ($start < 1) {
        $end = ($end + (1 - $start) > $this->page_count) ? $this->page_count : $end + (1 - $start);
        $start = 1;
      }
      if ($end > $this->page_count) {
        $start = ($start - ($end - $this->page_count) < 1) ? 1 : $start - ($end - $this->page_count);
        $end = $this->page_count;
      }
      return array($start, $end);
    }

    static function exception_handler($exception) {
      echo '<strong>Paginator Error: </strong>' . $exception->getMessage();
    }

  }

?>