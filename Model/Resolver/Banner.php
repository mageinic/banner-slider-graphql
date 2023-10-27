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

namespace MageINIC\BannerSliderGraphql\Model\Resolver;

use MageINIC\BannerSlider\Api\BannerRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\UrlInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Model\Template\FilterEmulate;

/**
 * Resolve Banner.
 */
class Banner implements ResolverInterface
{
    /**
     * @var BannerRepositoryInterface
     */
    private BannerRepositoryInterface $bannerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var ServiceOutputProcessor
     */
    private ServiceOutputProcessor $serviceOutputProcessor;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var FilterEmulate
     */
    private FilterEmulate $widgetFilter;

    /**
     * @var FilterBuilder
     */
    private FilterBuilder $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private FilterGroupBuilder $filterGroupBuilder;

    /**
     * @param BannerRepositoryInterface $bannerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param StoreManagerInterface $storeManager
     * @param FilterEmulate $widgetFilter
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(
        BannerRepositoryInterface $bannerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ServiceOutputProcessor $serviceOutputProcessor,
        StoreManagerInterface $storeManager,
        FilterEmulate $widgetFilter,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->bannerRepository   = $bannerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->storeManager = $storeManager;
        $this->widgetFilter = $widgetFilter;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed
     * @throws GraphQlInputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->validateArgs($args);
        $searchCriteria = $this->searchCriteriaBuilder->build('banner', $args);
        $searchCriteria->setCurrentPage($args['currentPage']);
        $searchCriteria->setPageSize($args['pageSize']);
        $sliderId = isset($value['slider_id']) ? $value['slider_id'] : $args['filter']['slider_id']['eq'];
        $filterSlider = $this->filterBuilder
            ->setField('slider_id')
            ->setConditionType('eq')
            ->setValue($sliderId)
            ->create();
        $filterGroup = $this->filterGroupBuilder->addFilter($filterSlider)->create();
        $searchCriteria->setFilterGroups([$filterGroup]);
        $searchResult = $this->bannerRepository->getList($searchCriteria);
        $postData = [
            "items" => [],
            "total_count" => $searchResult->getTotalCount()
        ];
        foreach ($searchResult->getItems() as $post) {
            $bannerData = $this->serviceOutputProcessor->process(
                $post,
                BannerRepositoryInterface::class,
                'getById'
            );
            $bannerData['media'] = $this->getBannerImage($post->getMedia());
            $bannerData['media_alt'] = $post->getMediaAlt();
            $bannerData['caption'] = $this->widgetFilter->filterDirective($post->getCaption());
            $postData["items"][] = $bannerData;
        }
        return $postData;
    }

    /**
     * Validate Args
     *
     * @param array $args
     * @throws GraphQlInputException
     */
    private function validateArgs(array $args): void
    {
        if (isset($args['currentPage']) && $args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }

        if (isset($args['pageSize']) && $args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
    }

    /**
     * Get Banner Image
     *
     * @param $image
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBannerImage($image): string
    {
        $url =$this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $url . 'MageINIC/bannerslider/' . $image;
    }
}
