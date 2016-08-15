<?php
/**
 * @file
 * Contains \Drupal\pusher\Form\ConfigureForm.
 */

namespace Drupal\pusher_integration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure form.
 */
class ConfigureForm extends ConfigFormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId() 
    {
        return 'pusher_configure_form';
    }

    /** 
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() 
    {
        return [
          'pusher_integration.settings',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) 
    {

        $config = $this->config('pusher_integration.settings');

        // General settings
        $form['pusher'] = array(
          '#type' => 'fieldset',
          '#title' => t('Pusher Settings'),
        );

        $form['pusher']['pusherAppId'] = array(
          '#type' => 'textfield',
          '#title' => t('Pusher App ID'),
          '#required' => false,
          '#default_value' => $config->get('pusherAppId') ? $config->get('pusherAppId') : '',
          '#description' => t('The Pusher App ID you created at Pusher.com.')
        );

        $form['pusher']['pusherAppKey'] = array(
          '#type' => 'textfield',
          '#title' => t('Pusher App Key'),
          '#required' => false,
          '#default_value' => $config->get('pusherAppKey') ? $config->get('pusherAppKey') : '',
          '#description' => t('The Pusher App Key you created at Pusher.com.')
        );

        $form['pusher']['pusherAppSecret'] = array(
          '#type' => 'textfield',
          '#title' => t('Pusher App Secret'),
          '#required' => false,
          '#default_value' => $config->get('pusherAppSecret') ? $config->get('pusherAppSecret') : '',
          '#description' => t('The Pusher App Secret you created at Pusher.com.')
        );

        $clusters = array(
          'mt1' => 'United States (us-east-1)',
          'eu1' => 'Europe (eu-west-1)',
          'ap1' => 'Asia (asia-southeast-1)'
        );

        $form['pusher']['clusterName'] = array(
          '#type' => 'select',
          '#options' => $clusters,
          '#title' => t('Pusher.com Cluster Name'),
          '#required' => false,
          '#default_value' => $config->get('clusterName') ? $config->get('clusterName') : '',
          '#description' => t('The Pusher.com cluster to connect with.')
        );

        $form['pusher']['defaultChannels'] = array(
          '#type' => 'textarea',
          '#title' => t('Default Channels'),
          '#required' => false,
          '#default_value' => $config->get('defaultChannels') ? $config->get('defaultChannels') : '',
          '#description' => t('List of channel names to autojoin when connecting. One channel name per line.')
        );

        $form['pusher']['channelPaths'] = array(
          '#type' => 'textarea',
          '#title' => t('Channel Routes'),
          '#required' => false,
          '#default_value' => $config->get('channelPaths') ? $config->get('channelPaths') : '',
          '#description' => t('Matches channels to specific routes (leave blank for all pages). CHANNEL_NAME|ROUTEPATTERN - One entry per line.'),
          '#placeholder' => t("e.g.\ntest-channel|/about/us")
        );

        $form['pusher']['createPrivateChannel'] = array(
          '#type' => 'checkbox',
          '#title' => t('Automatically create a private channel for authenticated users'),
          '#required' => false,
          '#default_value' => $config->get('createPrivateChannel'),
          '#description' => t('If a module that requires pusher_integration (this module) requires a private channel for authenticated users, check this box! Generally, there is no need.')
        );

        $form['pusher']['createPresenceChannel'] = array(
          '#type' => 'checkbox',
          '#title' => t('Automatically create a presence channel for authenticated users'),
          '#required' => false,
          '#default_value' => $config->get('createPresenceChannel'),
          '#description' => t('If a module that requires pusher_integration (this module) requires a presence channel for authenticated users, check this box!')
        );

        $form['pusher']['presenceChannelName'] = array(
          '#type' => 'textfield',
          '#title' => t('Name of Presence Channel'),
          '#required' => false,
          '#default_value' => $config->get('presenceChannelName') ? $config->get('presenceChannelName') : '',
          '#description' => t('The name of your presence channel (must be prefixed with "presence-" ... e.g. presence-chat, presence-general, presence-channel, etc.)')
        );

        $form['pusher']['debugLogging'] = array(
          '#type' => 'checkbox',
          '#title' => t('Enable debug logging to the page'),
          '#required' => false,
          '#default_value' => $config->get('debugLogging'),
          '#description' => t('It goes without saying that this should not be enabled in production environments! But if you need a quick and dirty debug log to watchdog, enable this.')
        );

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) 
    {
        parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) 
    {
        $config = $this->config('pusher_integration.settings');

        // General settings
        $config->set('pusherAppId', $form_state->getValue('pusherAppId'))
            ->set('pusherAppKey', $form_state->getValue('pusherAppKey'))
            ->set('pusherAppSecret', $form_state->getValue('pusherAppSecret'))
            ->set('clusterName', $form_state->getValue('clusterName'))
            ->set('defaultChannels', $form_state->getValue('defaultChannels'))
            ->set('channelPaths', $form_state->getValue('channelPaths'))
            ->set('createPrivateChannel', $form_state->getValue('createPrivateChannel'))
            ->set('createPresenceChannel', $form_state->getValue('createPresenceChannel'))
            ->set('presenceChannelName', $form_state->getValue('presenceChannelName'))
            ->set('debugLogging', $form_state->getValue('debugLogging'))
            ->save();


        parent::submitForm($form, $form_state);
    }
}
