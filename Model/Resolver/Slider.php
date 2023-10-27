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

use MageINIC\BannerSlider\Api\Data\SliderSearchResultsInterfaceFactory;
use MageINIC\BannerSlider\Api\SliderRepositoryInterface;
use MageINIC\BannerSlider\Model\ResourceModel\Slider\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Resolve Banner Slider.
 */
class Slider implements ResolverInterface
{
    /**
     * @var SliderRepositoryInterface
     */
    private $bannerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var ServiceOutputProcessor
     */
    private ServiceOutputProcessor $serviceOutputProcessor;

    /**
     * @var SliderSearchResultsInterfaceFactory
     */
    private SliderSearchResultsInterfaceFactory $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $timezone;

    /**
     * @param SliderRepositoryInterface $bannerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param SliderSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        SliderRepositoryInterface $bannerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ServiceOutputProcessor $serviceOutputProcessor,
        SliderSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        DateTime          $dateTime,
        TimezoneInterface $timezone,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->bannerRepository   = $bannerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
    }

    /**
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->validateArgs($args);
        $searchCriteria = $this->searchCriteriaBuilder->build('slider', $args);
        $searchCriteria->setCurrentPage($args['currentPage']);
        $searchCriteria->setPageSize($args['pageSize']);

        $currentDateTime = $this->dateTime->gmtDate();
        $collection =$this->collectionFactory->create();
        $collection->addFieldToFilter('start_date', ['lt' => $currentDateTime]);
        $collection->addFieldToFilter('end_date', ['gt' => $currentDateTime]);
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $postData = [
            "items" =>  [],
            "total_count" => $searchResults->getTotalCount()
        ];
        foreach ($searchResults->getItems() as $post) {
            $sliderData = $this->serviceOutputProcessor->process(
                $post,
                SliderRepositoryInterface::class,
                'getById'
            );
            $postData["items"][] = $sliderData;
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
}
