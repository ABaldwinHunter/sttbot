<?php
/**
 * Created by PhpStorm.
 * User: JC
 * Date: 2016-11-20
 * Time: 10:07
 */

namespace eidng8\Wiki\Templates;

use eidng8\Wiki\Template;

/**
 * InfoBox template parser
 *
 * @see http://startrektimelineswiki.com/wiki/Template:Infobox
 */
class InfoBox extends Template
{
    public function __construct(
        string $wikiText,
        string $type = Template::MISSION
    ) {
        parent::__construct($wikiText, "Infobox $type");
    }//end __construct()

    public function parse(): array
    {
        parent::parse();
        $mi = [];

        // extract title
        preg_match('/Box title\s*=\s*([^\[]+)/iu', $this->found[0], $title);
        $title = trim(strip_tags($title[1]));
        $mi['title'] = $title;

        // extract all items
        preg_match_all('/Row \d (.+?)\s= (.+?)$/imsu', $this->found[0], $info);

        $count = count($info[2]);
        $count = $count - $count % 2;
        for ($idx = 0; $idx < $count; $idx++) {
            $title = trim($info[2][$idx++]);
            $val = trim($info[2][$idx]);
            if (empty($title) || empty($val)) {
                continue;
            }

            $this->dehydrate($title, $val);
            $mi[$title] = $val;
        }//end foreach

        return $this->found = $mi;
    }//end parse()

    public function dehydrate(&$title, &$value): void
    {
        $trim = "{}[] \t\r\n\xb\0";
        $title = trim(strtolower($title), $trim);
        switch ($title) {
            case static::EPISODE:
                $value = explode('-', $value, 2);
                $value = end($value);
                $value = trim($value, $trim);
                break;

            case static::DISTRESS_CALLS:
                $value = trim($value, $trim);
                break;

            case static::CADET_CHALLENGE:
                $value = trim($value, $trim);
                break;

            case static::MISSION:
                $value = intval($value);
                break;

            case static::TYPE:
                $value = trim(strtolower($value), $trim);
                break;

            case static::COST:
                $value = MissionCost::load($value);
                break;
        }
    }//end dehydrate()

    public function name(): string
    {
        return $this->found[static::TITLE];
    }//end name()

    public function episode(): string
    {
        return $this->found[static::EPISODE]
            ?? $this->found[static::DISTRESS_CALLS]
            ?? $this->found[static::CADET_CHALLENGE] ?? null;
    }//end episode()

    public function sequence(): int
    {
        return $this->found[static::MISSION];
    }//end sequence()

    public function type(): string
    {
        return $this->found[static::TYPE];
    }//end type()

    public function cost(): MissionCost
    {
        return $this->found[static::COST];
    }//end cost()
}//end class
