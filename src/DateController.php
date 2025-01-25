<?php

/**
 * Jalali & Gregorian DateController module class;
 * 
 * @author: Mohammadamin Meghdadi
 * @email: mohamadamin.meghdadi@gmail.com
 * @license: MIT
 * @website: https://amin-developer.ir
 * @link: https://github.com/amin-developer2005
 */



namespace AminDeveloper2005\PhpDatecontrollerLibrary;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;


class DateController {


    private int $day;
    private int $month;
    private int $year;

    private int $days;


    protected const GREGORIAN_REFERENCE_DATE = '1970-01-01';
    protected const JALALI_REFERENCE_DATE = '1348-10-11';
    protected const INITIAL_GRIGORIAN_DATE = 1970;
    protected const INITIAL_JALALI_DATE = 1348;


    private array $_gregorianDaysInMonths = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    private array $_gregorianMonthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];


    private array $_jalaliDaysInMonths = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
    private array $_jalaliMonthNames = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];

    





    /**
     * Converts the Gregorian date to Jalali date based on the given format;
     * 
     * @param string $date The Gregorian date for converting to Jalali date;
     * @param string $format Your desired format for date converting;
     * @param string $timezone The timezone for the date conversion;
     * 
     * @return string The converted Jalali date in the specified format.
     */
    public function convertGregorianToJalali(string $date, string $format = "Y-M-d", string $timezone = 'GMT'): string  {
        $datetime = DateTime::createFromFormat('Y-m-d', $date);
        $dateTimeZone = new DateTimeZone($timezone);

        if(! $datetime) {
            throw new InvalidArgumentException("Invalid date format {$date}");
        }
        
        
        $gerigorianReferenceDate = new DateTime(self::GREGORIAN_REFERENCE_DATE, $dateTimeZone);
        $interval = $datetime->diff($gerigorianReferenceDate);
        $this->days = $interval->days;
        
        $this->day = 11;
        $this->month = 11;
        $this->year = self::INITIAL_JALALI_DATE;
       
        $this->days -= 19;


        while($this->days > $this->_jalaliDaysInMonths[$this->month - 1]) {

            $this->days -= $this->_jalaliDaysInMonths[$this->month - 1];
            $this->month++;            

            if($this->month == 13) {
                $this->year++;

                if($this->isLeapYear($this->year)) {
                    $this->days--;
                }

              $this->month = 1;
            }
                
        }


        return $this->formatDate($format, 'jal');
    }





      /**
     * Converts the Jalali date to Gregorian date based on the given format;
     * 
     * @param string $date The Jalali date for converting to Gregorian date;
     * @param string $format Your desired format for date converting;
     * @param string $timezone The timezone for the date conversion;
     * 
     * @return string The converted Gregorian date in the specified format.
     */
    public function convertJalaliToGregorian(string $date, string $format = 'Y-M-d', string $timezone = 'GMT'): string {
                
        $datetime = DateTime::createFromFormat('Y-m-d', $date);

        if(! $datetime) {
            throw new InvalidArgumentException("Invalid date format {$date}");
        }

        $dateTimeZone = new DateTimeZone($timezone);
        $jalaliReferenceDate = new DateTime(self::INITIAL_JALALI_DATE, $dateTimeZone);

        $datetime->setTimezone($dateTimeZone);
        $interval = $datetime->diff($jalaliReferenceDate);
        $daysPassedFromDate = $interval->days;

        $this->day = 1;
        $this->month = 1;
        $this->year = self::INITIAL_GRIGORIAN_DATE;

        $this->days = $daysPassedFromDate;


        for(; $this->days > $this->_gregorianDaysInMonths[$this->month - 1] ;) {
            
            $this->days -= $this->_gregorianDaysInMonths[$this->month - 1];
            $this->month++;

            if($this->month > 12) {
               $this->year++;

                if($this->isLeapYear($this->year)) {
                    $this->days--;
                }
               
                 $this->month = 1;
            }

        }


        return $this->formatDate($format, 'gre');
    }










    /**
     * Returns the name of the specific month based on the $monthNumber & $dateType;
     *
     * @param int $monthNumber => References the number of the specific month name in the $this->monthNames property;
     * @param string $dateType => Represents the type of date; if you pass {jal} it will give you the month name of Jalali date otherwise if you pass {gre} it will give you the month name of Gregorian date;  
     * 
     * @return string|null The name of the month.
     */
    private function fetchMonthName(int $monthNumber, string $dateType): ?string {
        return match($dateType) {
            'gre' => array_key_exists($monthNumber - 1, $this->_gregorianMonthNames) ? $this->_gregorianMonthNames[$monthNumber - 1] : null,
            'jal' => array_key_exists($monthNumber - 1, $this->_jalaliMonthNames) ? $this->_jalaliMonthNames[$monthNumber - 1] : null,
        };
    }




    /**
     * Converts a number to a two-digit string.
     *
     * @param int $number The number to convert;
     * 
     * @return string The two-digit string.
     */
    private function convertToDigitNumber(int $number): string {
        return $number >= 10 ? $number : '0' . $number;
    }



    /**
     * Formats the date based on the given format and date type.
     *
     * @param string $format The format for the date;
     * @param string $dateType The type of date ('jal' for Jalali, 'gre' for Gregorian);
     * 
     * @return string The formatted date.
     */
    private function formatDate(string $format, string $dateType) : string {
        return str_replace(
            ['Y', 'm', 'M', 'd'],
            [$this->year, $this->convertToDigitNumber($this->month), $this->fetchMonthName($this->month, $dateType), $this->convertToDigitNumber($this->days)],
            $format
        );
    }




    /**
     * Checks if the given year is a leap year.
     *
     * @param int $year The year to check;
     * 
     * @return bool True if the year is a leap year, false otherwise.
     */
    private function isLeapYear(int $year) : bool {
        return $year % 4 === 0 || $year % 400 === 0 && $year % 100 !== 0 ? true : false;
    }



}