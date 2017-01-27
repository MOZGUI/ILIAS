<?php
namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Renderer extends AbstractComponentRenderer {

	/**
	 * @inheritdoc
	 */
	public function render(Component\Component $component, RendererInterface $default_renderer) {
		$this->checkComponent($component);

		if ($component instanceof Component\Modal\Interruptive) {
			return $this->renderInterruptive($component, $default_renderer);
		} else if ($component instanceof Component\Modal\RoundTrip) {
			return $this->renderRoundTrip($component, $default_renderer);
		} else if ($component instanceof Component\Modal\Lightbox) {
			return $this->renderLightbox($component, $default_renderer);
		}
	}

	public function registerResources(ResourceRegistry $registry) {
		parent::registerResources($registry);
		$registry->register('./src/UI/templates/js/Modal/modal.js');
	}


	/**
	 * @param Component\Modal\Modal $modal
	 * @param string $id
	 */
	protected function registerSignals(Component\Modal\Modal $modal, $id) {
		$show = $modal->getShowSignal();
		$close = $modal->getCloseSignal();
		$this->getJavascriptBinding()->addOnLoadCode("$(document).on('{$show}', function(event, options) { il.UI.modal.showModal({$id}, options); });");
		$this->getJavascriptBinding()->addOnLoadCode("$(document).on('{$close}', function(event, options) { il.UI.modal.closeModal({$id}, options); });");
	}


	/**
	 * @param Component\Modal\Interruptive $modal
	 * @param RendererInterface $default_renderer
	 *
	 * @return string
	 */
	protected function renderInterruptive(Component\Modal\Interruptive $modal, RendererInterface $default_renderer) {
		$tpl = $this->getTemplate('tpl.interruptive.html', true, true);
		$id = $this->createId();
		$this->registerSignals($modal, $id);
		$this->triggerRegisteredSignals($modal, $id);
		$tpl->setVariable('ID', $id);
		$tpl->setVariable('FORM_ACTION', $modal->getFormAction());
		$tpl->setVariable('TITLE', $modal->getTitle());
		if ($modal->getMessage()) {
			$tpl->setCurrentBlock('with_message');
			$tpl->setVariable('MESSAGE', $modal->getMessage());
			$tpl->parseCurrentBlock();
		}
		if (count($modal->getAffectedItems())) {
			$tpl->setCurrentBlock('with_items');
			$titles = array_map(function ($interruptive_item) {
				return $interruptive_item->getTitle();
			}, $modal->getAffectedItems());
			$list = $this->getUIFactory()->listing()->unordered($titles);
			$tpl->setVariable('ITEMS', $default_renderer->render($list));
			foreach ($modal->getAffectedItems() as $item) {
				$tpl->setCurrentBlock('hidden_inputs');
				$tpl->setVariable('ITEM_ID', $item->getId());
				$tpl->parseCurrentBlock();
			}
		}
//		$action_button = $this->getUIFactory()->button()->primary($this->txt($modal->getActionButtonLabel()), '');
		$tpl->setVariable('ACTION_BUTTON_LABEL', $this->txt($modal->getActionButtonLabel()));
//		$cancel_button = $this->getCancelButton($modal->getCancelButtonLabel());
		$tpl->setVariable('CANCEL_BUTTON_LABEL', $this->txt($modal->getCancelButtonLabel()));
		return $tpl->get();
	}


	/**
	 * @param Component\Modal\RoundTrip $modal
	 * @param RendererInterface $default_renderer
	 *
	 * @return string
	 */
	protected function renderRoundTrip(Component\Modal\RoundTrip $modal, RendererInterface $default_renderer) {
		$tpl = $this->getTemplate('tpl.roundtrip.html', true, true);
		$id = $this->createId();
		$this->registerSignals($modal, $id);
		$this->triggerRegisteredSignals($modal, $id);
		$tpl->setVariable('ID', $id);
		$tpl->setVariable('TITLE', $modal->getTitle());
		foreach ($modal->getContent() as $content) {
			$tpl->setCurrentBlock('with_content');
			$tpl->setVariable('CONTENT', $default_renderer->render($content));
			$tpl->parseCurrentBlock();
		}
		foreach ($modal->getActionButtons() as $button) {
			$tpl->setCurrentBlock('with_buttons');
			$tpl->setVariable('BUTTON', $default_renderer->render($button));
			$tpl->parseCurrentBlock();
		}
//		$cancel_button = $this->getCancelButton($modal->getCancelButtonLabel());
		$tpl->setVariable('CANCEL_BUTTON_LABEL', $this->txt($modal->getCancelButtonLabel()));
		return $tpl->get();
	}


	/**
	 * @param Component\Modal\Lightbox $modal
	 * @param RendererInterface $default_renderer
	 *
	 * @return string
	 */
	protected function renderLightbox(Component\Modal\Lightbox $modal, RendererInterface $default_renderer) {
		$tpl = $this->getTemplate('tpl.lightbox.html', true, true);
		$id = $this->createId();
		$this->registerSignals($modal, $id);
		$this->triggerRegisteredSignals($modal, $id);
		$tpl->setVariable('ID', $id);
		$id_carousel = $this->createId();
		$pages = $modal->getPages();
		$tpl->setVariable('TITLE', $pages[0]->getTitle());
		$tpl->setVariable('ID_CAROUSEL', $id_carousel);
		if (count($pages) > 1) {
			$tpl->setCurrentBlock('has_indicators');
			foreach ($pages as $index => $page) {
				$tpl->setCurrentBlock('indicators');
				$tpl->setVariable('INDEX', $index);
				$tpl->setVariable('CLASS_ACTIVE', ($index == 0) ? 'active' : '');
				$tpl->setVariable('ID_CAROUSEL2', $id_carousel);
				$tpl->parseCurrentBlock();
			}
		}
		foreach ($pages as $i => $page) {
			$tpl->setCurrentBlock('pages');
			$tpl->setVariable('CLASS_ACTIVE', ($i == 0) ? ' active' : '');
			$tpl->setVariable('TITLE2', htmlentities($page->getTitle(), ENT_QUOTES, 'UTF-8'));
			$tpl->setVariable('CONTENT', $default_renderer->render($page->getComponent()));
			$tpl->setVariable('DESCRIPTION', $page->getDescription());
			$tpl->parseCurrentBlock();
		}
		if (count($pages) > 1) {
			$tpl->setCurrentBlock('controls');
			$tpl->setVariable('ID_CAROUSEL3', $id_carousel);
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable('ID_CAROUSEL4', $id_carousel);
		return $tpl->get();
	}


	/**
	 * Get a cancel button from the UI factory with the desired label by the modal
	 *
	 * @param string $txt_key
	 *
	 * @return Component\Button\Standard
	 */
//	protected function getCancelButton($txt_key) {
//		return $this->getUIFactory()->button()->standard($this->txt($txt_key), '');
//	}


	/**
	 * @inheritdoc
	 */
	protected function getComponentInterfaceName() {
		return array(
			Component\Modal\Interruptive::class,
			Component\Modal\RoundTrip::class,
			Component\Modal\Lightbox::class,
		);
	}
}
