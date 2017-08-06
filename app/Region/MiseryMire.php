<?php namespace ALttP\Region;

use ALttP\Item;
use ALttP\Location;
use ALttP\Region;
use ALttP\Support\LocationCollection;
use ALttP\World;

/**
 * Misery Mire Region and it's Locations contained within
 */
class MiseryMire extends Region {
	protected $name = 'Misery Mire';
	public $music_addresses = [
		0x155B9,
	];

	/**
	 * Create a new Misery Mire Region and initalize it's locations
	 *
	 * @param World $world World this Region is part of
	 *
	 * @return void
	 */
	public function __construct(World $world) {
		parent::__construct($world);

		$this->locations = new LocationCollection([
			new Location\BigChest("[dungeon-D6-B1] Misery Mire - big chest", 0xEA67, null, $this),
			new Location\Chest("[dungeon-D6-B1] Misery Mire - big hub room", 0xEA5E, null, $this),
			new Location\Chest("[dungeon-D6-B1] Misery Mire - big key", 0xEA6D, null, $this),
			new Location\Chest("[dungeon-D6-B1] Misery Mire - compass", 0xEA64, null, $this),
			new Location\Chest("[dungeon-D6-B1] Misery Mire - end of bridge", 0xEA61, null, $this),
			new Location\Chest("[dungeon-D6-B1] Misery Mire - map room", 0xEA6A, null, $this),
			new Location\Chest("[dungeon-D6-B1] Misery Mire - spike room", 0xE9DA, null, $this),
			new Location\Drop("Heart Container - Vitreous", 0x180158, null, $this),

			new Location\Prize\Crystal("Misery Mire Crystal", [null, 0x120A2, 0x53F48, 0x53F49, 0x180057, 0x180075, 0xC703], null, $this),
		]);

		$this->prize_location = $this->locations["Misery Mire Crystal"];
	}

	/**
	 * Set Locations to have Items like the vanilla game.
	 *
	 * @return $this
	 */
	public function setVanilla() {
		$this->locations["[dungeon-D6-B1] Misery Mire - big chest"]->setItem(Item::get('CaneOfSomaria'));
		$this->locations["[dungeon-D6-B1] Misery Mire - big hub room"]->setItem(Item::get('KeyD6'));
		$this->locations["[dungeon-D6-B1] Misery Mire - big key"]->setItem(Item::get('BigKeyD6'));
		$this->locations["[dungeon-D6-B1] Misery Mire - compass"]->setItem(Item::get('CompassD6'));
		$this->locations["[dungeon-D6-B1] Misery Mire - end of bridge"]->setItem(Item::get('KeyD6'));
		$this->locations["[dungeon-D6-B1] Misery Mire - map room"]->setItem(Item::get('MapD6'));
		$this->locations["[dungeon-D6-B1] Misery Mire - spike room"]->setItem(Item::get('KeyD6'));
		$this->locations["Heart Container - Vitreous"]->setItem(Item::get('BossHeartContainer'));

		$this->locations["Misery Mire Crystal"]->setItem(Item::get('Crystal6'));

		return $this;
	}

	/**
	 * Determine if the item being placed in this region can be placed here.
	 *
	 * @param Item $item item to test
	 *
	 * @return bool
	 */
	public function canFill(Item $item) : bool {
		if ($item instanceof Item\Key && !in_array($item, [Item::get('Key'), Item::get('KeyD6')])) {
			return false;
		}

		if ($item instanceof Item\BigKey && !in_array($item, [Item::get('BigKey'), Item::get('BigKeyD6')])) {
			return false;
		}

		if ($item instanceof Item\Map
			&& (!$this->world->config('region.mapsInDungeons', true)
				|| !in_array($item, [Item::get('Map'), Item::get('MapD6')]))) {
			return false;
		}

		if ($item instanceof Item\Compass
			&& (!$this->world->config('region.compassesInDungeons', true)
				|| !in_array($item, [Item::get('Compass'), Item::get('CompassD6')]))) {
			return false;
		}

		return true;
	}

	/**
	 * Initalize the requirements for Entry and Completetion of the Region as well as access to all Locations contained
	 * within for No Major Glitches
	 *
	 * @return $this
	 */
	public function initNoMajorGlitches() {
		$this->locations["[dungeon-D6-B1] Misery Mire - big chest"]->setRequirements(function($locations, $items) {
			return $items->has('BigKeyD6');
		})->setFillRules(function($item, $locations, $items) {
			return $item != Item::get('BigKeyD6');
		});

		$this->locations["[dungeon-D6-B1] Misery Mire - spike room"]->setRequirements(function($locations, $items) {
			return $items->has('Cape') || $items->has('CaneOfByrna');
		});

		$this->locations["[dungeon-D6-B1] Misery Mire - big hub room"]->setRequirements(function($locations, $items) {
			return $items->has('KeyD6');
		});

		$this->locations["[dungeon-D6-B1] Misery Mire - map room"]->setRequirements(function($locations, $items) {
			return $items->has('KeyD6');
		});

		$this->locations["[dungeon-D6-B1] Misery Mire - big key"]->setRequirements(function($locations, $items) {
			return $items->canLightTorches() && $items->has('KeyD6', 2);
		});

		$this->locations["[dungeon-D6-B1] Misery Mire - compass"]->setRequirements(function($locations, $items) {
			return $items->canLightTorches() && $items->has('KeyD6', 2);
		});

		$this->locations["Heart Container - Vitreous"]->setRequirements(function($locations, $items) {
			return $items->has('CaneOfSomaria') && $items->has('Lamp')
				&& $items->has('BigKeyD6') && $items->has('KeyD6', 2);
		})->setFillRules(function($item, $locations, $items) {
				if (!$this->world->config('region.bossNormalLocation', true)
					&& ($item instanceof Item\Key || $item instanceof Item\BigKey
						|| $item instanceof Item\Map || $item instanceof Item\Compass)) {
					return false;
				}

			if ($this->world->config('region.bossHaveKey', true)) {
				return $item != Item::get('BigKeyD6');
			}
			return !in_array($item, [Item::get('KeyD6'), Item::get('BigKeyD6')]);
		});

		$this->can_complete = function($locations, $items) {
			return $this->canEnter($locations, $items) && $items->has('CaneOfSomaria') && $items->has('Lamp');
		};

		$this->can_enter = function($locations, $items) {
			return ((($locations["Misery Mire Medallion"]->hasItem(Item::get('Bombos')) && $items->has('Bombos'))
					|| ($locations["Misery Mire Medallion"]->hasItem(Item::get('Ether')) && $items->has('Ether'))
					|| ($locations["Misery Mire Medallion"]->hasItem(Item::get('Quake')) && $items->has('Quake')))
				&& (config('game-mode') == 'swordless' || $items->hasSword()))
			&& $items->canLiftDarkRocks() && $items->has('MoonPearl') && $items->canFly()
			&& ($items->has('PegasusBoots') || $items->has('Hookshot'));
		};

		$this->prize_location->setRequirements($this->can_complete);

		return $this;
	}

	/**
	 * Initalize the requirements for Entry and Completetion of the Region as well as access to all Locations contained
	 * within for MajorGlitches Mode.
	 *
	 * @return $this
	 */
	public function initMajorGlitches() {
		$this->initNoMajorGlitches();

		$this->can_enter = function($locations, $items) {
			return ((($locations["Misery Mire Medallion"]->hasItem(Item::get('Bombos')) && $items->has('Bombos'))
					|| ($locations["Misery Mire Medallion"]->hasItem(Item::get('Ether')) && $items->has('Ether'))
					|| ($locations["Misery Mire Medallion"]->hasItem(Item::get('Quake')) && $items->has('Quake')))
				&& (config('game-mode') == 'swordless' || $items->hasSword()))
			&& ($items->has('PegasusBoots') || $items->has('Hookshot'))
			&& ($items->has('MoonPearl') || $items->hasABottle());
		};

		$this->can_complete = function($locations, $items) {
			return $this->canEnter($locations, $items)
				&& ($items->has('CaneOfSomaria')
					|| $items->hasSword() || $items->has('Hammer')
					|| ($items->has('Hookshot') && $items->has('Flippers') && (
						$items->has('FireRod') || $items->has('IceRod') || $items->canShootArrows()
					))
				);
		};

		$this->prize_location->setRequirements($this->can_complete);

		return $this;
	}

	/**
	 * Initalize the requirements for Entry and Completetion of the Region as well as access to all Locations contained
	 * within for Overworld Glitches Mode
	 *
	 * @return $this
	 */
	public function initOverworldGlitches() {
		$this->initNoMajorGlitches();

		$this->can_enter = function($locations, $items) {
			return ((($locations["Misery Mire Medallion"]->hasItem(Item::get('Bombos')) && $items->has('Bombos'))
					|| ($locations["Misery Mire Medallion"]->hasItem(Item::get('Ether')) && $items->has('Ether'))
					|| ($locations["Misery Mire Medallion"]->hasItem(Item::get('Quake')) && $items->has('Quake')))
				&& (config('game-mode') == 'swordless' || $items->hasSword()))
			&& $items->has('MoonPearl') && ($items->has('PegasusBoots') || $items->has('Hookshot'))
			&& $this->world->getRegion('Mire')->canEnter($locations, $items);
		};

		return $this;
	}
}
