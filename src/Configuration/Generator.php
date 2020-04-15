<?php declare(strict_types=1);

namespace phpbu\App\Configuration;

final class Generator
{
    /**
     * @var string
     */
    const TEMPLATE_XML = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<phpbu xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpbu.de/{phpbu_version}/phpbu.xsd"
         bootstrap="{bootstrap_script}"
         verbose="true">
    
    <!-- uncomment if you want to use an adapter
    <adapters>
    </adapters>
    -->
    
    <!-- uncomment if you want to use logging
    <logging>
    </logging>
    -->
    
    <backups>
        <backup name="__FIXME__">
            <source type="__FIXME__">
                <option name="__FIXME__" value="__FIXME__" />
            </source>
            <target dirname="__FIXME__" filename="backup-%Y%m%d-%H%i" compress="bzip2" />
        </backup>
    </backups>
</phpbu>

EOT;

    /**
     * @var string
     */
    const TEMPLATE_JSON = <<<EOT
{
  "bootstrap": "{bootstrap_script}",
  "verbose": true,
  "adapters": [
  ],
  "logging": [
  ],
  "backups": [
    {
      "source": {
        "type": "__FIXME__",
        "options": {
          "__FIXME__": "__FIXME__"
        }
      },
      "target": {
        "dirname": "__FIXME__",
        "filename": "backup-%Y%m%d-%H%i",
        "compress": "bzip2"
      }
    }
  ]
}

EOT;

    /**
     * Return the config file content
     *
     * @param  string $version
     * @param  string $format
     * @param  string $bootstrapScript
     * @return string
     */
    public function generateConfigurationSkeleton(string $version, string $format, string $bootstrapScript) : string
    {
        return \str_replace(
            [
                '{phpbu_version}',
                '{bootstrap_script}'
            ],
            [
                $version,
                $bootstrapScript
            ],
            $this->getTemplate($format)
        );
    }

    /**
     * Return the template code for the requested format
     *
     * @param  string $format
     * @return string
     */
    private function getTemplate(string $format)
    {
        return $format === 'json' ? self::TEMPLATE_JSON : self::TEMPLATE_XML;
    }
}
