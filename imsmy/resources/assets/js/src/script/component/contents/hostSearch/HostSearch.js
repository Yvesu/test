import React, { Component } from 'react'

import { Layout } from 'antd'

import Labels from '../tags/Labels'
import RankList from '../rankList/RankList'
import RankProgress from '../rankList/RankProgress'

//热门搜索

class HostSearch extends Component {
  render(){
    return(
    <Layout>
        <div style={{minHeight:"129px",background:"#fff",width:"100%"}}>
          <h3>热门搜索</h3>
          <Labels />
        </div>
        <div className="hostContent" style={{width:"100%",margin:"20px 0"}}>
          <div style={{width:"49.5%",float:"left"}}>
            <RankList uri="./data/rankh.json"/>
          </div>
          <div style={{width:"49.5%",float:"right"}}>
            <RankList uri="./data/rankhno.json" />
          </div>
        </div>
        <RankProgress />
    </Layout>
    )
  }
}

export default HostSearch
