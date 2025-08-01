import ctEvents from 'ct-events'
import { isTouchDevice } from './frontend/helpers/is-touch-device'
import { isIosDevice } from './frontend/helpers/is-ios-device'
import $ from 'jquery'

const loadSingleEntryPoint = ({
	els,
	events,
	forcedEvents,
	load,
	mount,
	condition,
	trigger,
}) => {
	if (!els) {
		els = []
	}

	if (!events) {
		events = []
	}

	if (!forcedEvents) {
		forcedEvents = []
	}

	if (!trigger) {
		trigger = []
	}

	/*
	[
		{
			id: 'click',
		},
	]
    */
	trigger = trigger.map((t) => {
		if (typeof t === 'string') {
			return {
				id: t,
			}
		}

		return t
	})

	if (!mount) {
		mount = ({ mount, el, ...everything }) =>
			el ? mount(el, everything) : mount()
	}

	if (els && {}.toString.call(els) === '[object Function]') {
		els = els()
	}

	const allEls = (Array.isArray(els) ? els : [els]).reduce(
		(a, selector) => [
			...a,
			...(Array.isArray(selector)
				? selector
				: typeof selector === 'string'
				? document.querySelectorAll(selector)
				: [selector]),
		],
		[]
	)

	if (allEls.length === 0) {
		return
	}

	if (
		condition &&
		!condition({
			els,
			allEls,
		})
	) {
		return
	}

	if (trigger.length === 0) {
		load().then((arg) => {
			allEls.map((el) => {
				mount({ ...arg, el })
			})
		})

		return
	}

	trigger.forEach((triggerDescriptor) => {
		if (triggerDescriptor.id === 'click') {
			allEls.map((el) => {
				if (el.hasLazyLoadClickListener) {
					return
				}

				el.hasLazyLoadClickListener = true

				el.addEventListener(
					'click',
					(event) => {
						// stopPropagation(). is here because on touch devices the
						// click event gets triggered for every child in the
						// actual el.
						//
						// In result, mount is triggered a couple times.
						//
						// Context: https://github.com/creative-themes/blocksy/issues/3374
						event.stopPropagation()

						event.preventDefault()

						load().then((arg) => mount({ ...arg, event, el }))
					},
					{
						...(triggerDescriptor.once
							? {
									once: true,
							  }
							: {}),
					}
				)
			})
		}

		if (triggerDescriptor.id === 'change') {
			allEls.map((el) => {
				if (el.hasLazyLoadChangeListener) {
					return
				}

				el.hasLazyLoadChangeListener = true

				const cb = (event) => {
					event.preventDefault()
					load().then((arg) => mount({ ...arg, event, el }))
				}

				if ($) {
					$(el).on('change', cb)
				} else {
					el.addEventListener('change', cb)
				}
			})
		}

		if (triggerDescriptor.id === 'scroll') {
			allEls.map((el) => {
				if (el.hasLazyLoadScrollListener) {
					return
				}

				el.hasLazyLoadScrollListener = true

				setTimeout(() => {
					let prevScroll = scrollY

					let cb = (event) => {
						// If the element was removed from the DOM before the
						// threshold was reached, we should remove the listener
						// to prevent memory leaks and to prevent calling the
						// mount on element being outside of DOM.
						if (!el.parentNode) {
							document.removeEventListener('scroll', cb)
							return
						}

						if (Math.abs(scrollY - prevScroll) > 30) {
							document.removeEventListener('scroll', cb)

							load().then((arg) => {
								return mount({ ...arg, event, el })
							})

							return
						}
					}

					document.addEventListener('scroll', cb)
				}, 500)
			})
		}

		if (triggerDescriptor.id === 'slight-mousemove') {
			if (!document.body.hasSlightMousemoveListenerTheme) {
				document.body.hasSlightMousemoveListenerTheme = true

				const cb = (event) => {
					allEls.map((el) => {
						load().then((arg) => mount({ ...arg, el }))
					})
				}

				document.addEventListener('mousemove', cb, { once: true })
				// document.addEventListener('touchstart', cb, { once: true })
			}
		}

		if (triggerDescriptor.id === 'input') {
			allEls.map((el) => {
				if (el.hasLazyLoadInputListener) {
					return
				}

				el.hasLazyLoadInputListener = true

				el.addEventListener(
					'input',
					(event) => load().then((arg) => mount({ ...arg, el })),
					{ once: true }
				)
			})
		}

		if (triggerDescriptor.id === 'hover-with-touch') {
			allEls.map((el) => {
				if (el.dataset.autoplay && parseFloat(el.dataset.autoplay)) {
					const elRect = el.getBoundingClientRect()

					if (
						// Ensure element is visible
						elRect.width > 0 &&
						!el.hasLazyLoadMouseOverAutoplayListener
					) {
						el.hasLazyLoadMouseOverAutoplayListener = true

						setTimeout(() => {
							load().then((arg) =>
								mount({
									...arg,
									el,
								})
							)
						}, 10)
					}

					return
				}

				if (el.hasLazyLoadMouseOverListener) {
					return
				}

				el.hasLazyLoadMouseOverListener = true

				el.forcedMount = (data = {}) =>
					load().then((arg) => mount({ ...arg, el, ...data }))
				;['mouseover', ...(isTouchDevice() ? ['touchstart'] : [])].map(
					(eventToRegister) => {
						el.addEventListener(
							eventToRegister,
							(event) => {
								if (event.type === 'touchstart') {
									document.addEventListener(
										'touchmove',
										() => {
											el.forcedMount({
												event,
											})
										},
										{
											once: true,
										}
									)
								} else {
									el.forcedMount({
										event,
									})
								}
							},
							{ once: true, passive: true }
						)
					}
				)
			})
		}

		if (triggerDescriptor.id === 'hover-with-click') {
			allEls.map((el) => {
				if (el.hasLazyLoadClickHoverListener) {
					return
				}

				el.hasLazyLoadClickHoverListener = true

				const l = (event) => {
					load().then((arg) =>
						mount({
							...arg,
							event,
							el,
						})
					)
				}

				// Only relevant for touch devices
				//
				// false | number | true
				let mouseOverState = false

				const isIgnored = (event) => {
					if (triggerDescriptor.ignoredEls) {
						return triggerDescriptor.ignoredEls.some((selector) => {
							return (
								event.target.closest(selector) ||
								event.target.matches(selector)
							)
						})
					}

					return false
				}

				el.addEventListener(
					'mouseover',
					(event) => {
						if (isIgnored(event)) {
							return
						}

						// Add delay to wait for potential click event
						// This should be done only on touch devices to facilitate
						// devices that have both touch and pointin capabilities.
						//
						// Need to wait 500ms specifically because that is how
						// much time is passing between mouseover and a full
						// click event.
						if (isTouchDevice()) {
							mouseOverState = setTimeout(() => {
								mouseOverState = true
								l(event)
							}, 500)
						}

						// Non touch device gets processed immediately, to not
						// make it wait.
						if (!isTouchDevice()) {
							l(event)
						}
					},
					{ once: true }
				)

				if (isTouchDevice()) {
					el.addEventListener(
						'click',
						(event) => {
							if (isIgnored(event)) {
								return
							}

							// Previously, iOS devices were handling such
							// behavior out of the box, but now it is
							// mandatory to prevent the default behavior of the
							// click event even there.
							event.preventDefault()

							if (mouseOverState === true) {
								return
							}

							if (mouseOverState !== false) {
								clearTimeout(mouseOverState)
							}

							l(event)
						},
						{ once: true }
					)
				}

				el.addEventListener('focus', l, { once: true })
			})
		}

		if (triggerDescriptor.id === 'hover') {
			allEls.map((el) => {
				if (el.hasLazyLoadMouseOverListener) {
					return
				}

				el.hasLazyLoadHoverListener = true

				el.addEventListener(
					'mouseover',
					(event) => {
						load().then((arg) =>
							mount({
								...arg,
								event,
								el,
							})
						)
					},
					{ once: true }
				)
			})
		}

		if (triggerDescriptor.id === 'submit') {
			allEls.map((el) => {
				if (el.hasLazyLoadSubmitListener) {
					return
				}

				el.hasLazyLoadSubmitListener = true

				el.addEventListener('submit', (event) => {
					if (
						event.submitter &&
						triggerDescriptor.ignoreSubmitter &&
						triggerDescriptor.ignoreSubmitter.find((selector) =>
							event.submitter.matches(selector)
						)
					) {
						return
					}

					event.preventDefault()
					load().then((arg) => mount({ ...arg, event, el }))
				})
			})
		}
	})
}

export const onDocumentLoaded = (cb) => {
	if (/comp|inter|loaded/.test(document.readyState)) {
		cb()
	} else {
		document.addEventListener('DOMContentLoaded', cb, false)
	}
}

export const handleEntryPoints = (mountEntryPoints, args) => {
	const { immediate = false, skipEvents = false } = args || {}

	if (!skipEvents) {
		;[
			...new Set(
				mountEntryPoints.reduce(
					(currentEvents, entry) => [
						...currentEvents,
						...(entry.events || []),
						...(entry.forcedEvents || []),
					],
					[]
				)
			),
		].map((distinctEvent) => {
			ctEvents.on(distinctEvent, () => {
				mountEntryPoints
					.filter(
						({ events = [] }) => events.indexOf(distinctEvent) > -1
					)
					.map((c) => loadSingleEntryPoint({ ...c, trigger: [] }))

				mountEntryPoints
					.filter(
						({ forcedEvents = [] }) =>
							forcedEvents.indexOf(distinctEvent) > -1
					)
					.map((entry) =>
						loadSingleEntryPoint({
							...entry,
							...(entry.forcedEventsElsSkip
								? {}
								: {
										els: ['body'],
								  }),
							condition: () => true,
							trigger: [],
						})
					)
			})
		})
	}

	const loadInitialEntryPoints = () => {
		mountEntryPoints
			.filter(({ onLoad = true }) => {
				if ({}.toString.call(onLoad) === '[object Function]') {
					return onLoad()
				}

				return !!onLoad
			})
			.map(loadSingleEntryPoint)
	}

	if (immediate) {
		loadInitialEntryPoints()
	} else {
		onDocumentLoaded(loadInitialEntryPoints)
	}
}

var loadCSS = function (href, before, media, attributes) {
	var doc = document
	var ss = doc.createElement('link')
	var ref

	if (before) {
		ref = before
	} else {
		var refs = (doc.body || doc.getElementsByTagName('head')[0]).childNodes
		ref = refs[refs.length - 1]
	}

	var sheets = doc.styleSheets
	ss.rel = 'stylesheet'
	ss.href = href
	// ss.media = 'only x'

	// ref.parentNode.insertBefore(ss, before ? ref : ref.nextSibling)
	document.body.appendChild(ss)

	var onloadcssdefined = function (cb) {
		var resolvedHref = ss.href
		var i = sheets.length
		while (i--) {
			if (sheets[i].href === resolvedHref) {
				return cb()
			}
		}
		setTimeout(function () {
			onloadcssdefined(cb)
		})
	}

	function loadCB() {
		if (ss.addEventListener) {
			ss.removeEventListener('load', loadCB)
		}
		// ss.media = media || 'all'
	}

	if (ss.addEventListener) {
		ss.addEventListener('load', loadCB)
	}
	ss.onloadcssdefined = onloadcssdefined
	onloadcssdefined(loadCB)
	return ss
}

function onloadCSS(ss, callback) {
	var called

	function newcb() {
		if (!called && callback) {
			called = true
			callback.call(ss)
		}
	}

	if (ss.addEventListener) {
		ss.addEventListener('load', newcb)
	}

	if (ss.attachEvent) {
		ss.attachEvent('onload', newcb)
	}

	if ('isApplicationInstalled' in navigator && 'onloadcssdefined' in ss) {
		ss.onloadcssdefined(newcb)
	}
}

export const loadStyle = (src, hasDisable = false) =>
	new Promise((resolve, reject) => {
		if (document.querySelector(`[href="${src}"]`)) {
			resolve()
			return
		}

		requestAnimationFrame(() => {
			const ss = loadCSS(src)

			onloadCSS(ss, () => {
				requestAnimationFrame(() => {
					resolve()
				})
			})
		})
	})
