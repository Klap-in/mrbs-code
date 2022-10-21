<?php
declare(strict_types=1);
namespace MRBS;

class DateTime extends \DateTime
{
  private static $isHoliday = array();
  private static $validHolidays = array();


  public function getDay() : int
  {
    return intval($this->format('j'));
  }


  public function getMonth() : int
  {
    return intval($this->format('n'));
  }


  public function getYear() : int
  {
    return intval($this->format('Y'));
  }


  // Checks whether the config setting of holidays for $year consists
  // of a valid set of dates.
  private static function validateHolidays(string $year) : bool
  {
    global $holidays;

    // Only need to validate a year once, so store the answer in a static property
    if (!isset(self::$validHolidays[$year]))
    {
      foreach ($holidays[$year] as $holiday)
      {
        $limits = explode('..', $holiday);

        // Check that the dates are valid
        foreach ($limits as $limit)
        {
          if (!validate_iso_date($limit))
          {
            self::$validHolidays[$year] = false;
            trigger_error("Invalid holiday date '$limit'");
            break 2;
          }
        }
      }
      self::$validHolidays[$year] = true;
    }

    return self::$validHolidays[$year];
  }


  // Determines whether the date is a holiday, as defined
  // in the config variable $holidays.
  private function isHolidayConfig() : bool
  {
    global $holidays;

    $year = $this->format('Y');
    $iso_date = $this->format('Y-m-d');

    // Only need to check if a date is a holiday once, so store the answer in a
    // static property
    if (!isset(self::$isHoliday[$iso_date]))
    {
      self::$isHoliday[$iso_date] = false;
      if (!empty($holidays[$year]) && self::validateHolidays($year))
      {
        foreach ($holidays[$year] as $holiday)
        {
          $limits = explode('..', $holiday);

          if (count($limits) == 1)
          {
            // It's a single date of the form '2022-01-01'
            if ($iso_date == $limits[0])
            {
              self::$isHoliday[$iso_date] = true;
              break;
            }
          }
          elseif (count($limits) == 2)
          {
            // It's a range of the form '2022-07-01..2022-07-31'
            if (($iso_date >= $limits[0]) && ($iso_date <= $limits[1]))
            {
              self::$isHoliday[$iso_date] = true;
              break;
            }
          }
          else
          {
            trigger_error("Invalid holiday element '$holiday'");
          }
        }
      }
    }

    return self::$isHoliday[$iso_date];
  }


  // Determines whether the date is a holiday.
  public function isHoliday() : bool
  {
    if ($this->isHolidayConfig())
    {
      return true;
    }

    // This method can be extended to check other sources, eg a .ics
    // file or a database table.
    return false;
  }


  public function isWeekend() : bool
  {
    return is_weekend($this->format('w'));
  }

}
