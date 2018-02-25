import React, { Component } from 'react'
import { Card, Badge } from 'antd'

class RankListMore extends Component {
  render(){
    return(
      <Card>
        <p className="rank_list_more_title">
          <span>机型</span>
          <span>新增用户</span>
          <span>新增用户(占比)</span>
          <span>启动次数</span>
          <span>启动次数（占比）</span>
        </p>
        <p className="rank_list_more_title">
          <span>iPhone 6</span>
          <span>1231232</span>
          <span>10.2%</span>
          <span>1231232</span>
          <span>10.2%</span>
        </p>
        <p className="rank_list_more_title">
          <span>iPhone 6 Plus</span>
          <span>1231232</span>
          <span>10.2%</span>
          <span>1231232</span>
          <span>10.2%</span>
        </p>
      </Card>
    )
  }
}

export default RankListMore
