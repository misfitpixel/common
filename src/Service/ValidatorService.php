<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 3/5/19
 * Time: 7:09 PM
 */

namespace MisfitPixel\Service;


use MisfitPixel\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ValidatorService
 * @package MisfitPixel\Service
 */
class ValidatorService
{
    /** @var ContainerInterface  */
    private $container;

    /**
     * ValidatorService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @return bool
     * @throws \Exception
     */
    public function validate(Request $request): bool
    {
        $data = json_decode($request->getContent(), true);

        if($data === null) {
            return false;
        }

        try {
            /**
             * load the schema file.
             */
            $schema = Yaml::parseFile(sprintf('%s/config/schema_validator/%s.yml',
                $this->container->get('kernel')->getProjectDir(),
                $request->get('_route')
            ));

            if($schema == null) {
                return true;
            }

        } catch(ParseException $e) {
            return false;
        }

        $this->validateNode($data, $schema['root'], $schema);

        return true;
    }

    /**
     * @param array $node
     * @param array $schemaNode
     * @param array $schema
     * @param string|null $child
     * @throws \Exception
     */
    private function validateNode(array $node, array $schemaNode, array $schema, string $child = null)
    {
        /**
         * validate required.
         */
        foreach($schemaNode as $schemaField => $rules) {
            if(
                (bool)$rules['required'] &&
                (!isset($node[$schemaField]) || $node[$schemaField] === null || $node[$schemaField] === "")
            ) {
                throw new Exception\InvalidFieldException(sprintf('Missing %s%s field',
                        ($child != null) ? sprintf('%s.', $child) : '',
                        $this->getFriendlyFieldName($schemaField)
                ), $schemaField);
            }
        }

        foreach($node as $field => $value) {
            if(isset($schemaNode[$field])) {
                $params = $schemaNode[$field];

                /**
                 * validate enum list.
                 */
                if(
                    isset($params['in']) &&
                    is_array($params['in']) &&
                    !in_array($node[$field], $params['in'])
                ) {
                    throw new Exception\InvalidFieldException(sprintf('Invalid field value for %s%s; must be one of %s',
                        ($child != null) ? sprintf('%s.', $child) : '',
                        $field,
                        implode(', ', $params['in'])
                    ), $field);
                }

                /**
                 * validate types.
                 */
                if(isset($params['type'])) {
                    if(
                        $params['type'] === 'string' &&
                        (
                            (isset($params['min']) && strlen($node[$field]) < $params['min']) ||
                            (isset($params['max']) && strlen($node[$field]) > $params['max']) ||
                            (isset($params['pattern']) && !(bool)preg_match(sprintf('%s', $params['pattern']), $node[$field]))
                        )
                    ) {
                        throw new Exception\InvalidFieldException(sprintf('Invalid field value for %s%s',
                            ($child != null) ? sprintf('%s.', $child) : '',
                            $field
                        ), $field);
                    }

                    if(
                        $params['type'] === 'int' &&
                        (
                            !is_numeric($node[$field]) ||
                            (
                                (isset($params['min']) && $node[$field] < $params['min']) ||
                                (isset($params['max']) && $node[$field] > $params['max'])
                            )
                        )
                    ) {
                        throw new Exception\InvalidFieldException(sprintf('Invalid field value for %s%s',
                            ($child != null) ? sprintf('%s.', $child) : '',
                            $field
                        ), $field);
                    }

                    if(
                        $params['type'] === 'float' &&
                        (
                            !is_numeric($node[$field]) ||
                            (
                                (isset($params['min']) && $node[$field] < $params['min']) ||
                                (isset($params['max']) && $node[$field] > $params['max'])
                            )
                        )
                    ) {
                        throw new Exception\InvalidFieldException(sprintf('Invalid field value for %s%s',
                            ($child != null) ? sprintf('%s.', $child) : '',
                            $field
                        ), $field);
                    }

                    if(
                        in_array($params['type'], ['array', 'object']) &&
                        !is_array($node[$field])
                    ) {
                        throw new Exception\InvalidFieldException(sprintf('Invalid field value for %s%s',
                            ($child != null) ? sprintf('%s.', $child) : '',
                            $field
                        ), $field);
                    }

                    /**
                     * verify that the child schema is valid.
                     */
                    if(
                        in_array($params['type'], ['array', 'object']) &&
                        (
                            isset($params['schema']) &&
                            !isset($schema[$params['schema']])
                        )
                    ) {
                        throw new Exception\InvalidFieldException(sprintf('Invalid validation config for %s%s',
                            ($child != null) ? sprintf('%s.', $child) : '',
                            $field
                        ), $field);
                    }

                    /**
                     * validate child schema.
                     */
                    if(
                        in_array($params['type'], ['array']) &&
                        is_array($node[$field]) &&
                        isset($params['schema'])
                    ) {
                        foreach($node[$field] as $index => $item) {
                            $this->validateNode($item, $schema[$params['schema']], $schema, $params['schema']);
                        }
                    }

                    if(
                        in_array($params['type'], ['object']) &&
                        is_array($node[$field]) &&
                        isset($params['schema'])
                    ) {
                        $this->validateNode($node[$field], $schema[$params['schema']], $schema, $params['schema']);
                    }
                }
            }
        }
    }

    /**
     * @param string $field
     * @return string
     */
    private function getFriendlyFieldName(string $field)
    {
        return str_replace('_', ' ', $field);
    }
}