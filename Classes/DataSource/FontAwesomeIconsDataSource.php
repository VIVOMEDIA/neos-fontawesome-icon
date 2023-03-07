<?php
namespace VIVOMEDIA\FontAwesome\Icon\DataSource;

use Composer\Semver\Comparator;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Symfony\Component\Yaml\Yaml;

class FontAwesomeIconsDataSource extends AbstractDataSource
{
    /**
     * @var string
     */
    static protected $identifier = 'vivomedia-font-awesome-icons';

    const STYLE_SOLID = 'solid';
    const STYLE_LIGHT = 'light';
    const STYLE_REGULAR = 'regular';
    const STYLE_BRAND = 'brands';
    const STYLE_DUOTONE = 'duotone';

    protected $styleCode = [
        self::STYLE_SOLID => 'fas',
        self::STYLE_LIGHT => 'fal',
        self::STYLE_REGULAR => 'far',
        self::STYLE_BRAND => 'fab',
        self::STYLE_DUOTONE => 'dab'
    ];

    /**
     * @Flow\InjectConfiguration()
     */
    protected $configuration;

    public function getData(NodeInterface $node = null, array $arguments = [])
    {
        $installedVersion = $this->configuration['version'] ?? '5.0.0';
        $licence = in_array($this->configuration['licence'], ['free','pro'], true) ? $this->configuration['licence'] : 'free';

        $editorOptionValues = [];
        $iconMetaData = $this->loadIconMetaData($licence);

        foreach ($iconMetaData as $name => $data) {
            $lowestChangeVersion = current($data['changes']);
            if (!Comparator::lessThanOrEqualTo($lowestChangeVersion, $installedVersion)) {
                continue;
            }

            foreach ($data['styles'] as $style) {
                // Skip disabled Styles
                if(isset($this->configuration['disabled'][$style]) && $this->configuration['disabled'][$style]){
                    continue;
                }
                
                $optionKey = $this->getIconCode($style, $name);
                $editorOptionValues[$optionKey] = [
                    'label' => $data['label'],
                    'group' => $style,
                    'icon' => $this->getIconCode($style, $name)
                ];
            }

        }
        return $editorOptionValues;
    }

    protected function loadIconMetaData(string $currentLicence) : array
    {
        $fileName = sprintf( 'resource://VIVOMEDIA.FontAwesome.Icon/Private/Metadata/icons-%s.yml',  $currentLicence);
        return (array) Yaml::parseFile($fileName);
    }

    protected function getIconCode($style, $name)
    {
        $styleCode = $this->styleCode[$style] ?? $this->styleCode[self::STYLE_SOLID];
        return sprintf('%s fa-%s', $styleCode, $name);
    }
}
