<?php
/**
 * MageINIC
 * Copyright (C) 2023 MageINIC <support@mageinic.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://opensource.org/licenses/gpl-3.0.html.
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category MageINIC
 * @package MageINIC_BannerSliderGraphql
 * @copyright Copyright (c) 2023 MageINIC (https://www.mageinic.com/)
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MageINIC <support@mageinic.com>
 */

namespace MageINIC\BannerSliderGraphql\Model\Resolver\Block;

use MageINIC\BannerSlider\Model\Slider;
use MageINIC\BannerSlider\Api\Data\SliderInterface;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Get identities from resolved data
 */
class Identity implements IdentityInterface
{
    private $cacheTag = Slider::CACHE_TAG;

    /**
     * Get identity tags from resolved data
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        $items = $resolvedData['items'] ?? [];
        foreach ($items as $item) {
            if (is_array($item) && !empty($item[SliderInterface::SLIDER_ID])) {
                $ids[] = sprintf('%s_%s', $this->cacheTag, $item[SliderInterface::SLIDER_ID]);
            }
        }
        if (!empty($ids)) {
            $ids[]= $this->cacheTag;
        }
        return $ids;
    }
}
