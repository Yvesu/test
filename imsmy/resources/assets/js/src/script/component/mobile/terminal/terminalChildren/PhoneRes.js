import React, { Component } from 'react'
import { Layout } from 'antd'
import RankList from '../rankList/RankList'
import RankListMore from '../rankList/RankListMore'

class PhoneRes extends Component {
  render(){
    return(
      <Layout>
        <h2 style={{fontSize:20}}>分辨率 Top 10</h2>
        <div style={{margin:"20px 0",width:"100%"}}>
            <div style={{width:"49.5%",float:"left"}}>
              <RankList uri="./data/mobile/rankmobile.json" />
            </div>
            <div style={{width:"49.5%",float:"right"}}>
              <RankList uri="./data/mobile/rankmobileone.json" />
            </div>
        </div>
        <div style={{minHeight:200}}>
            <h2 style={{fontSize:20,marginBottom:20}}>数据明细（ <b>18</b> 种分辨率）</h2>
            <RankListMore />
        </div>
      </Layout>
    )
  }
}
export default PhoneRes
