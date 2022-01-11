import $ from 'jquery';

class Search {
    //1. describe and create/initiate our object
    constructor() {
        this.addSearchHTML();
        this.openButton = $('.js-search-trigger');
        this.closeButton = $('.search-overlay__close');
        this.searchOverlay = $('.search-overlay');
        this.searchField = $('#search-term');
        this.searchResults = $('#search-overlay__results');
        this.body = $('body');
        this.isOverlayOpen = false;
        this.isLoading = false;
        this.prevValue;
        this.typingTimer;
        this.events();
    }

    //2. events
    events() {
        this.openButton.on('click', this.openOverlay.bind(this));
        this.closeButton.on('click', this.closeOverlay.bind(this));
        this.searchField.on('keyup', this.typingLogic.bind(this));
        $(document).on('keydown', this.keyPressDispatcher.bind(this));
    }

    //3. methods
    openOverlay() {
        this.searchOverlay.addClass('search-overlay--active');
        this.body.addClass('body-no-scroll');
        this.isOverlayOpen = true;
        setTimeout(() => this.searchField.focus(), 301);
        return false;
    }

    closeOverlay() {
        this.searchOverlay.removeClass('search-overlay--active');
        this.body.removeClass('body-no-scroll');
        this.isOverlayOpen = false;
        this.searchField.val('');
        this.searchResults.html('');
    }

    keyPressDispatcher(e) {
        if(e.keyCode === 83 && !this.isOverlayOpen && !$('input, textarea').is(':focus')) {
            this.openOverlay();
        }
        if(e.keyCode === 27 && this.isOverlayOpen) {
            this.closeOverlay();
        }
    }

    typingLogic() {
        if(this.searchField.val() !== this.prevValue) {
            clearTimeout(this.typingTimer);

            if(this.searchField.val()) {

                if(!this.isLoading) {
                    this.searchResults.html('<div class="spinner-loader"></div>');
                    this.isLoading = true;
                }

                this.typingTimer = setTimeout(this.getResults.bind(this), 500);
            } else {
                this.searchResults.html('');
                this.isLoading = false;
            }
        }

        this.prevValue = this.searchField.val();
    }

    getResults() {
        $.getJSON(universityData.root_url + '/wp-json/university/v1/search?search=' + this.searchField.val(), (data) => {
            this.searchResults.html(`
                <div class="row">
                <div class="one-third">
                    <h2 class="search-overlay__section-title">General Information</h2>
                    ${data.generalInfo.length ? '<ul class="link-list min-list">' : '<p>No general information matches that search.</p>'}
                        ${data.generalInfo.map(item=>`<li><a href="${item.permalink}">${item.title}</a>${item.postType == 'post' ? ' by ' + item.authorName : ''}</li>`).join('')}
                    ${data.generalInfo.length ? '</ul>' : ''}
                </div>
                
                <div class="one-third">
                    <h2 class="search-overlay__section-title">Programs</h2>
                    ${data.programs.length ? '<ul class="link-list min-list">' : `<p>No program matches that search. <a href="${universityData.root_url}/programs">View all programs.</a></p>`}
                        ${data.programs.map(item=>`<li><a href="${item.permalink}">${item.title}</a></li>`).join('')}
                    ${data.programs.length ? '</ul>' : ''}
                    
                    <h2 class="search-overlay__section-title">Professors</h2>
                    ${data.professors.length ? '<ul class="professor-cards">' : '<p>No professor matches that search.</p>'}
                        ${data.professors.map(item=>`
                            <li class="professor-card__list-item">
                                <a class="professor-card" href="${item.permalink}">
                                    <img class="professor-card__image" src="${item.image}">
                                    <span class="professor-card__name">${item.title}<span>
                                </a>
                            </li>
                        `).join('')}
                    ${data.professors.length ? '</ul>' : ''}
                </div>
                
                <div class="one-third">
                    <h2 class="search-overlay__section-title">Campuses</h2>
                    ${data.campuses.length ? '<ul class="link-list min-list">' : `<p>No campus matches that search. <a href="${universityData.root_url}/campuses">View all campuses.</a></p>`}
                        ${data.campuses.map(item=>`<li><a href="${item.permalink}">${item.title}</a></li>`).join('')}
                    ${data.campuses.length ? '</ul>' : ''}
                    
                    <h2 class="search-overlay__section-title">Events</h2>
                    ${data.events.length ? '' : `<p>No event matches that search. <a href="${universityData.root_url}/events">View all events.</a></p>`}
                    ${data.events.map(item=>`
                    <div class="event-summary">
                        <a class="event-summary__date t-center" href="${item.permalink}">
                            <span class="event-summary__month">${item.month}</span>
                            <span class="event-summary__day">${item.day}</span>
                        </a>
                        <div class="event-summary__content">
                            <h5 class="event-summary__title headline headline--tiny"><a href="${item.permalink}">${item.title}</a></h5>
                            <p>${item.description}<a href="${item.permalink}" class="nu gray">Learn more</a></p>
                        </div>
                    </div>
                    `).join('')}
                </div>
                </div>
            `);
            this.isLoading = false;
        });
    }

    addSearchHTML() {
        $('body').append(`
            <div class="search-overlay">
                <div class="search-overlay__top">
                    <div class="container">
                        <i class="fa fa-search search-overlay__icon" aria-hidden="true"></i>
                        <input type="text" class="search-term" placeholder="What are you looking for?" id="search-term">
                        <i class="fa fa-window-close search-overlay__close"></i>
                    </div>
                </div>
            
                <div class="container">
                    <div id="search-overlay__results">
            
                    </div>
                </div>
            </div>
        `);
    }
}

export default Search;