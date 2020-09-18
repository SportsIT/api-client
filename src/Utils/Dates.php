<?php

namespace Dash\Utils;

use Carbon\Carbon;

class Dates {
  const FORMAT = 'Y-m-d\TH:i:s';

  /**
   * @param string|\DateTimeInterface $date
   *
   * @return string
   */
  public static function format($date): string {
    return Carbon::parse($date)->format(static::FORMAT);
  }
}
