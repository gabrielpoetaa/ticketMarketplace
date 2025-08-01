import Component from './component-component'
import Portal from './portal'
import { wrapEvent } from './utils'
import {
	createElement,
	useEffect,
	useState,
	Fragment,
} from '@wordpress/element'

let createAriaHider = (dialogNode) => {
	let originalValues = []
	let rootNodes = []

	Array.prototype.forEach.call(
		document.querySelectorAll('body > *'),
		(node) => {
			if (node === dialogNode.parentNode) {
				return
			}
			let attr = node.getAttribute('aria-hidden')
			let alreadyHidden = attr !== null && attr !== 'false'
			if (alreadyHidden) {
				return
			}
			originalValues.push(attr)
			rootNodes.push(node)
			node.setAttribute('aria-hidden', 'true')
		}
	)

	return () => {
		rootNodes.forEach((node, index) => {
			let originalValue = originalValues[index]
			if (originalValue === null) {
				node.removeAttribute('aria-hidden')
			} else {
				node.setAttribute('aria-hidden', originalValue)
			}
		})
	}
}

let k = () => {}

let portalDidMount = (refs, initialFocusRef) => {
	refs.disposeAriaHider = createAriaHider(refs.overlayNode)
}

let contentWillUnmount = ({ refs }) => {
	refs.disposeAriaHider()
}

let FocusContext = React.createContext()

let DialogOverlay = React.forwardRef(
	(
		{
			container,
			isOpen = true,
			onDismiss = k,
			initialFocusRef,
			onClick,
			onKeyDown,
			...props
		},
		forwardRef
	) => (
		<Component>
			{isOpen ? (
				<Portal container={container} data-reach-dialog-wrapper>
					<Component
						refs={{ overlayNode: null, contentNode: null }}
						didMount={({ refs }) => {
							portalDidMount(refs, initialFocusRef)
						}}
						willUnmount={contentWillUnmount}>
						{({ refs }) => (
							<FocusContext.Provider
								value={(node) => (refs.contentNode = node)}>
								<div
									data-reach-dialog-overlay
									onClick={wrapEvent(onClick, (event) => {
										event.stopPropagation()
										onDismiss()
									})}
									onKeyDown={wrapEvent(onKeyDown, (event) => {
										if (event.key === 'Escape') {
											event.stopPropagation()
											onDismiss()
										}
									})}
									ref={(node) => {
										refs.overlayNode = node
										forwardRef && forwardRef(node)
									}}
									{...props}
								/>
							</FocusContext.Provider>
						)}
					</Component>
				</Portal>
			) : null}
		</Component>
	)
)

DialogOverlay.propTypes = {
	initialFocusRef: () => {},
}

let stopPropagation = (event) => event.stopPropagation()

let DialogContent = React.forwardRef(
	({ onClick, onKeyDown, ...props }, forwardRef) => (
		<FocusContext.Consumer>
			{(contentRef) => (
				<div
					aria-modal="true"
					data-reach-dialog-content
					tabIndex="-1"
					onClick={wrapEvent(onClick, stopPropagation)}
					ref={(node) => {
						contentRef(node)
						forwardRef && forwardRef(node)
					}}
					{...props}
				/>
			)}
		</FocusContext.Consumer>
	)
)

let Dialog = ({ container, isOpen, onDismiss = k, ...props }) => (
	<DialogOverlay container={container} isOpen={isOpen} onDismiss={onDismiss}>
		<DialogContent {...props} />
	</DialogOverlay>
)

export { DialogOverlay, DialogContent, Dialog }
