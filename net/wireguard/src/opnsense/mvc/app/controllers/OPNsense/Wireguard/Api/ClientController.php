<?php

/**
 *    Copyright (C) 2018 Michael Muenz <m.muenz@gmail.com>
 *
 *    All rights reserved.
 *
 *    Redistribution and use in source and binary forms, with or without
 *    modification, are permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 *    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 *    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 *    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *    POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace OPNsense\Wireguard\Api;

use OPNsense\Base\ApiMutableModelControllerBase;
use OPNsense\Core\Backend;

class ClientController extends ApiMutableModelControllerBase
{
    protected static $internalModelName = 'client';
    protected static $internalModelClass = '\OPNsense\Wireguard\Client';

    public function searchClientAction()
    {
        return $this->searchBase('clients.client', array("enabled", "name", "pubkey", "tunneladdress", "serveraddress", "serverport", "dnsaddress"));
    }
    public function getClientAction($uuid = null)
    {
        $this->sessionClose();
        return $this->getBase('client', 'clients.client', $uuid);
    }
    public function addClientAction($uuid = null)
    {
        if ($this->request->isPost() && $this->request->hasPost("client")) {
            if ($uuid != null) {
                $node = $this->getModel()->getNodeByReference('clients.client.' . $uuid);
            } else {
                $node = $this->getModel()->clients->client->Add();
            }
            $node->setNodes($this->request->getPost("client"));
            if (empty((string)$node->pubkey) && empty((string)$node->privkey)) {
                // generate new keypair
                $backend = new Backend();
                $keyspriv = $backend->configdpRun("wireguard genkey", 'private');
                $keyspub = $backend->configdpRun("wireguard genkey", 'public');
                $node->privkey = $keyspriv;
                $node->pubkey = $keyspub;
            }
            return $this->validateAndSave($node, 'client');
        }
        return array("result" => "failed");
    }
    public function delClientAction($uuid)
    {
        return $this->delBase('clients.client', $uuid);
    }
    public function setClientAction($uuid = null)
    {
        if ($this->request->isPost() && $this->request->hasPost("client")) {
            if ($uuid != null) {
                $node = $this->getModel()->getNodeByReference('clients.client.' . $uuid);
            } else {
                $node = $this->getModel()->clients->client->Add();
            }
            $node->setNodes($this->request->getPost("client"));
            if (empty((string)$node->pubkey) && empty((string)$node->privkey)) {
                // generate new keypair
                $backend = new Backend();
                $keyspriv = $backend->configdpRun("wireguard genkey", 'private');
                $keyspub = $backend->configdpRun("wireguard genkey", 'public');
                $node->privkey = $keyspriv;
                $node->pubkey = $keyspub;
            }
            return $this->validateAndSave($node, 'client');
        }
        return array("result" => "failed");
    }
    public function toggleClientAction($uuid)
    {
        return $this->toggleBase('clients.client', $uuid);
    }
}

