<?php

function universityRegisterSearch() {
    register_rest_route('university/v1', 'search', array(
        'methods' => WP_REST_SERVER::READABLE,
        'callback' => 'universitySearchResults'
    ));
}

function universitySearchResults($data) {
    $mainQuery = new WP_Query(array(
        'post_type' => array('post', 'page', 'professor', 'event', 'program', 'campus'),
        's' => sanitize_text_field($data['search'])
    ));

    $queryResults = array(
        'generalInfo' => array(),
        'professors' => array(),
        'programs' => array(),
        'events' => array(),
        'campuses' => array()
    );

    while($mainQuery->have_posts()) {
        $mainQuery->the_post();
        if(get_post_type() == 'post' OR get_post_type() == 'page') {
            array_push($queryResults['generalInfo'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink(),
                'postType' => get_post_type(),
                'authorName' => get_the_author()
            ));
        }

        if(get_post_type() == 'professor') {
            array_push($queryResults['professors'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink(),
                'image' => get_the_post_thumbnail_url(0, 'professorLandscape')
            ));
        }

        if(get_post_type() == 'program') {
            $relatedCampuses = get_field('related_campus');

            if($relatedCampuses) {
                foreach($relatedCampuses as $campus) {
                    array_push($queryResults['campuses'], array(
                        'title' => get_the_title($campus),
                        'permalink' => get_the_permalink($campus)
                    ));
                }
            }

            array_push($queryResults['programs'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink(),
                'id' => get_the_id()
            ));
        }

        if(get_post_type() == 'event') {
            $eventDate = new DateTime(get_field('event_date'));

            array_push($queryResults['events'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink(),
                'month' => $eventDate->format('M'),
                'day' => $eventDate->format('d'),
                'description' => has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 18)
            ));
        }

        if(get_post_type() == 'campus') {
            array_push($queryResults['campuses'], array(
                'title' => get_the_title(),
                'permalink' => get_the_permalink()
            ));
        }

    }

    if($queryResults['programs']) {
        $programsMetaQuery = array('relation' => 'OR');

        foreach($queryResults['programs'] as $item) {
            array_push($programsMetaQuery, array(
                'key' => 'related_programs',
                'compare' => 'LIKE',
                'value' => '"' . $item['id'] . '"',
            ));
        }

        $programRelationship = new WP_Query(array(
            'post_type' => array('professor', 'event'),
            'meta_query' => $programsMetaQuery
        ));

        while($programRelationship->have_posts()) {
            $programRelationship->the_post();

            if(get_post_type() == 'professor') {
                array_push($queryResults['professors'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'image' => get_the_post_thumbnail_url(0, 'professorLandscape')
                ));
            }

            if(get_post_type() == 'event') {
                $eventDate = new DateTime(get_field('event_date'));

                array_push($queryResults['events'], array(
                    'title' => get_the_title(),
                    'permalink' => get_the_permalink(),
                    'month' => $eventDate->format('M'),
                    'day' => $eventDate->format('d'),
                    'description' => has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 18)
                ));
            }
        }

        $queryResults['professors'] = array_values(array_unique($queryResults['professors'], SORT_REGULAR));
        $queryResults['events'] = array_values(array_unique($queryResults['events'], SORT_REGULAR));

    }

    return $queryResults;
}

add_action('rest_api_init', 'universityRegisterSearch');
