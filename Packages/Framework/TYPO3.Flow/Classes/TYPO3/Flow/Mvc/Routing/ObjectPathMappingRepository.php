<?php
namespace TYPO3\Flow\Mvc\Routing;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Repository for object path mapping objects
 * @see \TYPO3\Flow\Mvc\Routing\ObjectPathMapping
 *
 * @Flow\Scope("singleton")
 */
class ObjectPathMappingRepository extends \TYPO3\Flow\Persistence\Repository {

	/**
	 * @var string
	 */
	const ENTITY_CLASSNAME = 'TYPO3\Flow\Mvc\Routing\ObjectPathMapping';

	/**
	 * @var array
	 */
	protected $defaultOrderings = array(
		'objectType' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING,
		'uriPattern' => \TYPO3\Flow\Persistence\QueryInterface::ORDER_ASCENDING
	);

	/**
	 * @param string $objectType the object type of the ObjectPathMapping object
	 * @param string $uriPattern the URI pattern of the ObjectPathMapping object
	 * @param string $pathSegment the URI path segment of the ObjectPathMapping object
	 * @return \TYPO3\Flow\Mvc\Routing\ObjectPathMapping
	 */
	public function findOneByObjectTypeUriPatternAndPathSegment($objectType, $uriPattern, $pathSegment) {
		$query = $this->createQuery();
		return $query->matching(
			$query->logicalAnd(
				$query->equals('objectType', $objectType),
				$query->equals('uriPattern', $uriPattern),
				$query->equals('pathSegment', $pathSegment)
			)
		)
		->execute()
		->getFirst();
	}

	/**
	 * @param string $objectType the object type of the ObjectPathMapping object
	 * @param string $uriPattern the URI pattern of the ObjectPathMapping object
	 * @param mixed $identifier the identifier of the object, for example the UUID, @see \TYPO3\Flow\Persistence\PersistenceManagerInterface::getIdentifierByObject()
	 * @return \TYPO3\Flow\Mvc\Routing\ObjectPathMapping
	 * @throws \InvalidArgumentException
	 */
	public function findOneByObjectTypeUriPatternAndIdentifier($objectType, $uriPattern, $identifier) {
		// TODO support "complex" identifiers (see http://forge.typo3.org/issues/29979)
		if (!is_string($identifier)) {
			throw new \InvalidArgumentException('Only identifiers of type "string" are supported currently. "' . (is_object($identifier) ? get_class($identifier) : gettype($identifier)) . '" given.', 1316354957);
		}
		$query = $this->createQuery();
		return $query->matching(
			$query->logicalAnd(
				$query->equals('objectType', $objectType),
				$query->equals('uriPattern', $uriPattern),
				$query->equals('identifier', $identifier)
			)
		)
		->execute()
		->getFirst();
	}

}

?>
