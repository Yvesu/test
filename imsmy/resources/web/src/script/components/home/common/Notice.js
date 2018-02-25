import React, { Component } from 'react'
import NoticeIcon from 'ant-design-pro/lib/NoticeIcon'

class Notice extends Component{
  render(){
    return(
      <div style={{marginLeft:15,marginTop:-3}}>
        <NoticeIcon count={5} />

      </div>

    )
  }
}
export default Notice
